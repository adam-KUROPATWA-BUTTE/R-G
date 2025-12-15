<?php
// Compat PHP 5.6: pas de declare(strict_types), pas de "??", pas de ": array"

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/functions.php';

// Base path (compat sous-dossier)
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/';
$base_path = rtrim(str_replace('\\','/', dirname($scriptName)), '/');
if ($base_path === '/') $base_path = '';

function users_schema() {
    $pdo = db();
    $cols = array();
    try {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $pdo->query("PRAGMA table_info(users)");
            foreach ($stmt->fetchAll() as $row) {
                $cols[] = is_array($row) ? (isset($row['name']) ? $row['name'] : (isset($row[1]) ? $row[1] : '')) : '';
            }
        } else {
            $stmt = $pdo->query("SHOW COLUMNS FROM users");
            foreach ($stmt->fetchAll() as $row) {
                $cols[] = is_array($row) ? (isset($row['Field']) ? $row['Field'] : (isset($row[0]) ? $row[0] : '')) : '';
            }
        }
    } catch (Exception $e) {
        $cols = array('id','email','password');
    }
    // Nettoyage
    $tmp = array();
    foreach ($cols as $c) {
        if (is_string($c) && $c !== '') $tmp[] = $c;
    }
    $cols = $tmp;

    $has = function($c) use ($cols) { return in_array($c, $cols, true); };
    $pick = function($candidates) use ($has) {
        foreach ($candidates as $c) if ($has($c)) return $c;
        return null;
    };

    return array(
        'id'      => $pick(array('id','user_id')),
        'first'   => $pick(array('first_name','prenom','firstname','first')),
        'last'    => $pick(array('last_name','nom','lastname','last')),
        'email'   => $pick(array('email','mail','user_email','login','username')),
        'pass'    => $pick(array('password_hash','password','pwd','motdepasse')),
        'created' => $pick(array('created_at','createdAt','date_creation','date_created','created')),
        'allCols' => $cols,
    );
}

$errors = array();
$success = '';
$values = array('first_name'=>'','last_name'=>'','email'=>'');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();

        // Récup & trim (compat PHP 5.6 → pas de "??")
        $first = isset($_POST['first_name']) ? trim((string)$_POST['first_name']) : '';
        $last  = isset($_POST['last_name'])  ? trim((string)$_POST['last_name'])  : '';
        $email = isset($_POST['email'])      ? trim((string)$_POST['email'])      : '';
        $pass  = isset($_POST['password'])   ? (string)$_POST['password']         : '';
        $pass2 = isset($_POST['password_confirm']) ? (string)$_POST['password_confirm'] : '';

        $values = array('first_name'=>$first, 'last_name'=>$last, 'email'=>$email);

        // Validations
        $namePattern = "/^[\\p{L} '-]{1,60}$/u";
        if ($first !== '' && !preg_match($namePattern, $first)) $errors[] = "Prénom invalide.";
        if ($last !== ''  && !preg_match($namePattern, $last))  $errors[] = "Nom invalide.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = "Email invalide.";
        if (strlen($pass) < 8) $errors[] = "Mot de passe: minimum 8 caractères.";
        if ($pass !== $pass2)  $errors[] = "La confirmation du mot de passe ne correspond pas.";

        // Schéma users
        $sch = users_schema();
        if (!$sch['email']) $errors[] = "Aucune colonne email/mail/login trouvée dans la table users.";
        if (!$sch['pass'])  $errors[] = "Aucune colonne mot de passe trouvée (password_hash/password/pwd/motdepasse).";

        if (!$errors) {
            $pdo = db();

            // Unicité email
            $selIdCol = $sch['id'] ? $sch['id'] : 'id';
            $stmt = $pdo->prepare("SELECT {$selIdCol} FROM users WHERE {$sch['email']} = ? LIMIT 1");
            $stmt->execute(array($email));
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $now  = date('Y-m-d H:i:s');

                // Construire l'INSERT selon colonnes présentes
                $fields = array();
                $ph     = array();
                $params = array();

                if ($sch['first'] && $first !== '') { $fields[] = $sch['first']; $ph[]='?'; $params[] = $first; }
                if ($sch['last']  && $last  !== '') { $fields[] = $sch['last'];  $ph[]='?'; $params[] = $last;  }

                $fields[] = $sch['email']; $ph[]='?'; $params[] = $email;
                $fields[] = $sch['pass'];  $ph[]='?'; $params[] = $hash;

                if ($sch['created']) { $fields[] = $sch['created']; $ph[]='?'; $params[] = $now; }

                $sql = "INSERT INTO users (".implode(', ', $fields).") VALUES (".implode(', ', $ph).")";
                $ins = $pdo->prepare($sql);
                $ins->execute($params);

                // Auto-login
                $userId = (int)$pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;

                header("Location: {$base_path}/");
                exit;
            }
        }
    } catch (Exception $e) {
        $errors[] = "Erreur: " . $e->getMessage();
    }
}

$page_title = 'Créer un compte';
require __DIR__ . '/partials/header.php';
?>
<main class="main-content" style="max-width:640px;margin:2rem auto;">
  <h1>Créer un compte</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <ul><?php foreach ($errors as $er): ?><li><?= htmlspecialchars($er) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form" novalidate>
    <?= csrf_field() ?>
    <label>Prénom (optionnel)
      <input type="text" name="first_name" maxlength="60" value="<?= htmlspecialchars($values['first_name']) ?>">
    </label>
    <label>Nom (optionnel)
      <input type="text" name="last_name" maxlength="60" value="<?= htmlspecialchars($values['last_name']) ?>">
    </label>
    <label>Email
      <input type="email" name="email" required maxlength="190" value="<?= htmlspecialchars($values['email']) ?>">
    </label>
    <label>Mot de passe (min 8)
      <input type="password" name="password" required>
    </label>
    <label>Confirmez le mot de passe
      <input type="password" name="password_confirm" required>
    </label>
    <div style="margin-top:1rem;">
      <button class="btn-primary" type="submit">Créer mon compte</button>
    </div>
  </form>
</main>
<?php require __DIR__ . '/partials/footer.php';