<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Routine;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

// --- 1. AUTHENTIFICATION & RÉCUPÉRATION ---
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) return response()->json(['message' => 'Identifiants incorrects'], 401);
    return response()->json(['user' => $user]);
});

Route::post('/forgot-password', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    if (!$user) return response()->json(['message' => 'Adresse e-mail inconnue'], 404);
    $token = Str::random(60);
    DB::table('password_reset_tokens')->updateOrInsert(['email' => $request->email],['token' => Hash::make($token), 'created_at' => now()]);
    $url = "http://localhost:4200/reset-password?token={$token}&email={$request->email}";
    try {
        Mail::to($user->email)->send(new ResetPasswordMail($url, $user->name));
        return response()->json(['message' => 'Un e-mail de récupération a été envoyé !']);
    } catch (\Exception $e) { return response()->json(['message' => "Erreur d'envoi"], 500); }
});

Route::post('/reset-password', function (Request $request) {
    $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();
    if (!$reset || !Hash::check($request->token, $reset->token)) return response()->json(['message' => 'Lien invalide'], 400);
    User::where('email', $request->email)->update(['password' => Hash::make($request->password)]);
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
    return response()->json(['message' => 'Mot de passe modifié !']);
});

// --- 2. GESTION DES EMPLOYÉS & DOCUMENTS ---
Route::get('/users', function () { return response()->json(User::all()); });
Route::get('/users/{id}', function ($id) { return User::findOrFail($id); });
Route::post('/users', function (Request $request) {
    return User::create(['name'=>$request->name,'email'=>$request->email,'password'=>Hash::make($request->password),'role'=>$request->role,'poste'=>$request->poste,'solde_conges'=>30]);
});
Route::put('/users/{id}', function (Request $request, $id) {
    User::findOrFail($id)->update($request->only(['name','email','role','poste','fiche_poste']));
    return response()->json(['ok' => true]);
});
Route::delete('/users/{id}', function ($id) {
    $u = User::findOrFail($id);
    if ($u->email === 'admin@test.com') return response()->json(['message' => 'Interdit'], 403);
    $u->delete();
    return response()->json(['ok' => true]);
});
Route::post('/users/{id}/upload', function (Request $request, $id) {
    $u = User::findOrFail($id);
    if ($request->hasFile('document')) {
        $path = $request->file('document')->store('documents', 'public');
        $type = $request->type; $u->$type = $path; $u->save();
        return response()->json(['path' => $path]);
    }
    return response()->json(['error' => true], 400);
});

// --- 3. PRÉSENCE & POINTAGE ---
Route::get('/presence/check/{userId}', function ($userId) { return response()->json(DB::table('presences')->where('user_id', $userId)->where('date', now()->toDateString())->first()); });
Route::post('/presence/arrivee', function (Request $request) { 
    DB::table('presences')->insert(['user_id'=>$request->user_id,'date'=>now()->toDateString(),'heure_arrivee'=>now()->format('H:i:s'),'statut'=>(now()->hour >= 9)?'en retard':'present','created_at'=>now()]); 
    // Notifier l'admin
    $user = User::find($request->user_id);
    DB::table('notifications')->insert(['message' => $user->name . ' a pointé son arrivée', 'type' => 'inscription', 'created_at' => now()]);
    return response()->json(['ok'=>true]); 
});
Route::post('/presence/depart', function (Request $request) { DB::table('presences')->where('user_id',$request->user_id)->where('date',now()->toDateString())->update(['heure_depart'=>now()->format('H:i:s')]); return response()->json(['ok'=>true]); });
Route::get('/presence/history/{userId}', function ($userId) { return response()->json(DB::table('presences')->where('user_id', $userId)->whereMonth('date', now()->month)->get()); });

// --- 4. ROUTINES & LOGS ---
Route::get('/routines', function () { return response()->json(Routine::orderBy('heure', 'asc')->get()); });
Route::get('/routines/{poste}', function ($poste) { return response()->json(Routine::where('poste', $poste)->orderBy('heure', 'asc')->get()); });
Route::post('/routines', function (Request $request) { return response()->json(Routine::create($request->all())); });
Route::delete('/routines/{id}', function ($id) { Routine::destroy($id); return response()->json(['ok'=>true]); });

Route::get('/users/{id}/routines-done', function ($id) { return response()->json(DB::table('routine_logs')->where('user_id', $id)->where('date_du_jour', now()->toDateString())->pluck('routine_id')); });
Route::post('/routines/log', function (Request $request) {
    $d = ['user_id'=>$request->user_id, 'routine_id'=>$request->routine_id, 'date_du_jour'=>now()->toDateString()];
    $e = DB::table('routine_logs')->where($d)->first();
    if ($e) DB::table('routine_logs')->where('id', $e->id)->delete();
    else DB::table('routine_logs')->insert(array_merge($d, ['created_at'=>now()]));
    return response()->json(['ok'=>true]);
});

// --- 5. RÈGLEMENT & PROCÉDURES ---
Route::get('/reglement', function () { return response()->json(DB::table('reglements')->first()); });
Route::post('/reglement', function (Request $request) { DB::table('reglements')->updateOrInsert(['id' => 1], ['contenu' => $request->contenu, 'updated_at' => now()]); return response()->json(['ok' => true]); });
Route::get('/users/{id}/check-reglement', function ($id) { $v = DB::table('reglement_user')->where('user_id', $id)->first(); return response()->json(['valide' => !!$v, 'date' => $v?->date_validation, 'signature' => $v?->signature]); });
Route::post('/users/{id}/accepter-reglement', function (Request $request, $id) { 
    DB::table('reglement_user')->updateOrInsert(['user_id' => $id], ['date_validation' => now(), 'signature' => $request->signature]); 
    // Notifier l'admin
    $user = User::find($id);
    DB::table('notifications')->insert(['message' => $user->name . ' a signé le règlement', 'type' => 'signature', 'created_at' => now()]);
    return response()->json(['ok' => true]); 
});

Route::get('/procedures', function () { return response()->json(DB::table('procedures')->get()); });
Route::post('/procedures', function (Request $request) { DB::table('procedures')->insert(array_merge($request->all(), ['created_at' => now()])); return response()->json(['ok' => true]); });
Route::delete('/procedures/{id}', function ($id) { DB::table('procedures')->where('id', $id)->delete(); return response()->json(['ok' => true]); });

// --- 6. CONGÉS ---
Route::get('/conges/all', function () { return response()->json(DB::table('conges')->join('users', 'conges.user_id', '=', 'users.id')->select('conges.*', 'users.name as employee_name')->orderBy('date_debut', 'asc')->get()); });
Route::get('/conges/user/{id}', function ($id) { return response()->json(DB::table('conges')->where('user_id', $id)->get()); });
Route::post('/conges', function (Request $request) {
    DB::table('conges')->insert(array_merge($request->all(), ['statut' => 'en attente', 'created_at' => now()]));
    // Notifier l'admin
    $user = User::find($request->user_id);
    DB::table('notifications')->insert(['message' => 'Nouveau congé de ' . $user->name, 'type' => 'conge', 'created_at' => now()]);
    return response()->json(['ok' => true]);
});
Route::put('/conges/{id}/status', function (Request $request, $id) {
    $c = DB::table('conges')->where('id', $id)->first();
    if ($request->statut === 'approuve') {
        $diff = (new \DateTime($c->date_debut))->diff(new \DateTime($c->date_fin))->days + 1;
        DB::table('users')->where('id', $c->user_id)->decrement('solde_conges', $diff);
    }
    DB::table('conges')->where('id', $id)->update(['statut' => $request->statut]);
    return response()->json(['ok' => true]);
});
Route::delete('/conges/{id}', function ($id) { DB::table('conges')->where('id', $id)->delete(); return response()->json(['ok' => true]); });

// --- 7. ADMIN STATS, NOTIFICATIONS & LEADERBOARD (NOUVEAU & SYNC ✅) ---

Route::get('/admin/stats', function () {
    $t = User::where('role', '!=', 'admin')->count();
    return response()->json([
        'total_employees' => $t,
        'reglement_percent' => ($t > 0) ? round((DB::table('reglement_user')->count() / $t) * 100) : 0,
        'fiches_percent' => ($t > 0) ? round((User::where('fiche_validee', true)->count() / $t) * 100) : 0,
        'tasks_today' => DB::table('routine_logs')->where('date_du_jour', now()->toDateString())->count()
    ]);
});

Route::get('/admin/notifications/count', function () {
    $count = DB::table('notifications')->where('lu', false)->count();
    return response()->json(['total_notifications' => $count]);
});

Route::get('/admin/notifications', function () {
    return response()->json(DB::table('notifications')->orderBy('created_at', 'desc')->take(10)->get());
});

Route::post('/admin/notifications/read-all', function () {
    DB::table('notifications')->update(['lu' => true]);
    return response()->json(['ok' => true]);
});

Route::get('/admin/leaderboard', function () {
    $users = User::where('role', '!=', 'admin')->get();
    $res = [];
    foreach ($users as $u) {
        $tot = Routine::where('poste', $u->poste)->count();
        $don = DB::table('routine_logs')->where('user_id', $u->id)->where('date_du_jour', now()->toDateString())->count();
        $res[] = [ 'id' => $u->id, 'name' => $u->name, 'poste' => $u->poste, 'score' => ($tot > 0) ? round(($don / $tot) * 100) : 0, 'total_primes' => DB::table('primes')->where('user_id', $u->id)->sum('montant') ];
    }
    usort($res, fn($a, $b) => $b['score'] <=> $a['score']);
    return response()->json($res);
});

// --- 8. PRIMES ---
Route::post('/admin/attribuer-prime', function (Request $request) { DB::table('primes')->insert(array_merge($request->all(), ['periode' => now()->format('F Y'), 'created_at' => now()])); return response()->json(['ok' => true]); });
Route::get('/admin/primes/user/{id}', function ($id) { return response()->json(DB::table('primes')->where('user_id', $id)->orderBy('created_at','desc')->get()); });
Route::put('/admin/primes/{id}', function (Request $request, $id) { DB::table('primes')->where('id', $id)->update($request->only('montant')); return response()->json(['ok' => true]); });
Route::delete('/admin/primes/{id}', function ($id) { DB::table('primes')->where('id', $id)->delete(); return response()->json(['ok' => true]); });

// Route temporaire pour créer les tables sur le serveur
Route::get('/install-database', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh');
        return "Base de données installée avec succès !";
    } catch (\Exception $e) {
        return "Erreur : " . $e->getMessage();
    }
});

Route::get('/create-storage-link', function () {
    $target = storage_path('app/public');
    $shortcut = public_path('storage');
    if (file_exists($shortcut)) {
        return "Le lien existe déjà.";
    }
    symlink($target, $shortcut);
    return "Lien de stockage créé avec succès !";
});

Route::get('/clear-cache', function() {
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    return "Cache nettoyé !";
});