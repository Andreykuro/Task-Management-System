<?php

session_start();


if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}
if (!isset($_SESSION['next_task_id'])) {
    $_SESSION['next_task_id'] = 1;
}

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? null;
$role     = $_SESSION['role'] ?? null;

$allUsers       = ['admin', 'student1', 'student2'];
$adminOnlyPages = ['about', 'team', 'users'];
$validPages     = ['dashboard', 'view_tasks', 'add_task', 'about', 'team', 'users'];


$page = $_GET['page'] ?? ($loggedIn ? 'dashboard' : 'login');
if (!$loggedIn) {
    $page = 'login'; // unauthenticated users only ever see the login screen
}

$loginError  = '';
$taskSuccess = '';
$taskError   = '';

// eto ung "login" block of code
if ($page === 'login') {
    if ($loggedIn) {
        header('Location: header.php?page=dashboard');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $u = trim($_POST['username'] ?? '');
        $p = trim($_POST['password'] ?? '');

        // Hardcoded credentials
        $credentials = [
            'admin'    => ['password' => 'admin123', 'role' => 'admin'],
            'russell' => ['password' => 'pass123',  'role' => 'student'],
            'jericson' => ['password' => 'pass123',  'role' => 'student'],
        ];

        if (isset($credentials[$u]) && $credentials[$u]['password'] === $p) {
            $_SESSION['username']  = $u;
            $_SESSION['role']      = $credentials[$u]['role'];
            $_SESSION['logged_in'] = true;
            header('Location: header.php?page=dashboard');
            exit();
        } else {
            $loginError = 'Invalid username or password.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Task Manager</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <div class="logo-container">
                        <span style="font-size:50px;">📋</span>
                    </div>
                    <h1>Task Manager</h1>
                    <p class="subtitle">Sign in to manage your tasks</p>
                    <p class="university-name">ITCC1023 - Web Systems and Technologies I</p>
                </div>

                <?php if ($loginError): ?>
                    <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>

                <form method="POST" action="header.php?page=login">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-login">🔐 Login</button>
                    <button type="reset" class="btn-reset">Clear</button>
                </form>

                <div class="login-info">
                    <span class="demo-label">Demo Accounts</span>
                    <strong>Admin:</strong> admin / admin123<br>
                    <strong>Student:</strong> russell / pass123<br>
                    <strong>Student:</strong> jericson / pass123
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// $loggedin ts is where you see the things when you're logged on

if (!in_array($page, $validPages, true)) {
    $page = 'dashboard';
}

if (in_array($page, $adminOnlyPages, true) && $role !== 'admin') {
    header('Location: header.php?page=dashboard');
    exit();
}

// view, complete delete actions fuh yeah
if ($page === 'view_tasks' && isset($_GET['action'], $_GET['id'])) {
    $id     = (int) $_GET['id'];
    $action = $_GET['action'];

    foreach ($_SESSION['tasks'] as $index => $task) {
        if ($task['id'] === $id) {
            $isOwner = $task['owner'] === $username;

            if ($action === 'complete' && ($role === 'admin' || $isOwner)) {
                $_SESSION['tasks'][$index]['status'] = 'complete';
            }
            if ($action === 'delete' && $role === 'admin') {
                unset($_SESSION['tasks'][$index]);
                $_SESSION['tasks'] = array_values($_SESSION['tasks']);
            }
            break;
        }
    }
    header('Location: header.php?page=view_tasks');
    exit();
}

// ----- add_task: form submission -----
if ($page === 'add_task' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $owner       = $role === 'admin' ? ($_POST['assigned_to'] ?? '') : $username;

    if ($title === '') {
        $taskError = 'Task title is required.';
    } elseif ($role === 'admin' && !in_array($owner, $allUsers, true)) {
        $taskError = 'Please select a valid user to assign the task to.';
    } else {
        $_SESSION['tasks'][] = [
            'id'           => $_SESSION['next_task_id'],
            'title'        => $title,
            'description'  => $description,
            'owner'        => $owner,
            'status'       => 'pending',
            'date_created' => date('Y-m-d H:i'),
        ];
        $_SESSION['next_task_id']++;
        $taskSuccess = 'Task added successfully!';
    }
}

$pageTitles = [
    'dashboard'  => 'Dashboard - Task Manager',
    'view_tasks' => 'View Tasks - Task Manager',
    'add_task'   => 'Add Task - Task Manager',
    'about'      => 'About - Task Manager',
    'team'       => 'Team - Task Manager',
    'users'      => 'Users - Task Manager',
];
$page_title = $pageTitles[$page];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <h1>📋 Task Manager</h1>
            </div>
            <div class="user-info">
                <span>👋 Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>!</span>
                <span class="role-badge <?php echo $role; ?>">
                    <?php echo ucfirst($role); ?>
                </span>
                <a href="logout.php" class="btn-logout">🚪 Logout</a>
            </div>
        </header>

        <nav class="nav-menu">
            <a href="header.php?page=dashboard" <?php echo $page === 'dashboard' ? 'class="active"' : ''; ?>>🏠 Dashboard</a>
            <a href="header.php?page=view_tasks" <?php echo $page === 'view_tasks' ? 'class="active"' : ''; ?>>📋 View Tasks</a>
            <a href="header.php?page=add_task" <?php echo $page === 'add_task' ? 'class="active"' : ''; ?>>➕ Add Task</a>
            <?php if ($role === 'admin'): ?>
                <a href="header.php?page=about" <?php echo $page === 'about' ? 'class="active"' : ''; ?>>ℹ️ About</a>
                <a href="header.php?page=team" <?php echo $page === 'team' ? 'class="active"' : ''; ?>>👥 Team</a>
                <a href="header.php?page=users" <?php echo $page === 'users' ? 'class="active"' : ''; ?>>👥 Users</a>
            <?php endif; ?>
        </nav>

<?php
// ===================== PAGE CONTENT =====================

if ($page === 'dashboard') {

    if ($role === 'admin') {
        $myTasks = $_SESSION['tasks'];
    } else {
        $myTasks = array_filter($_SESSION['tasks'], function ($t) use ($username) {
            return $t['owner'] === $username;
        });
    }
    $total     = count($myTasks);
    $pending   = count(array_filter($myTasks, function ($t) { return $t['status'] === 'pending'; }));
    $completed = count(array_filter($myTasks, function ($t) { return $t['status'] === 'complete'; }));
    ?>
    <div class="content">
        <h2>🏠 Dashboard</h2>

        <?php if ($role === 'admin'): ?>
            <div class="role-notice admin-notice">
                👋 Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong>! You have full access to manage all users and tasks.
            </div>
        <?php else: ?>
            <div class="role-notice student-notice">
                👋 Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong>! Here's an overview of your tasks.
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label"><?php echo $role === 'admin' ? 'Total Tasks (All Users)' : 'Total Tasks'; ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $pending; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card completed">
                <div class="stat-number"><?php echo $completed; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <a href="header.php?page=view_tasks" class="btn-primary">📋 View Tasks</a>
            <a href="header.php?page=add_task" class="btn-secondary">➕ Add Task</a>
            <?php if ($role === 'admin'): ?>
                <a href="header.php?page=users" class="btn-secondary">👥 Manage Users</a>
            <?php endif; ?>
        </div>
    </div>
    <?php

} elseif ($page === 'view_tasks') {

    if ($role === 'admin') {
        $tasks = $_SESSION['tasks'];
    } else {
        $tasks = array_filter($_SESSION['tasks'], function ($t) use ($username) {
            return $t['owner'] === $username;
        });
    }
    ?>
    <div class="content">
        <h2>📋 View Tasks</h2>

        <?php if ($role === 'admin'): ?>
            <div class="role-notice admin-notice">Showing tasks for all users.</div>
        <?php else: ?>
            <div class="role-notice student-notice">Showing your own tasks only.</div>
        <?php endif; ?>

        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <p>No tasks yet.</p>
                <a href="header.php?page=add_task" class="btn-primary">➕ Add a Task</a>
            </div>
        <?php else: ?>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <?php if ($role === 'admin'): ?><th>Created By</th><?php endif; ?>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['title']); ?></td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <?php if ($role === 'admin'): ?>
                                <td><?php echo htmlspecialchars($task['owner']); ?></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($task['status'] === 'complete'): ?>
                                    <span class="status-badge complete">✅ Complete</span>
                                <?php else: ?>
                                    <span class="status-badge pending">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($task['status'] !== 'complete'): ?>
                                    <a href="header.php?page=view_tasks&action=complete&id=<?php echo $task['id']; ?>" class="btn-complete">✔ Complete</a>
                                <?php else: ?>
                                    <span class="completed-text">Done</span>
                                <?php endif; ?>
                                <?php if ($role === 'admin'): ?>
                                    <a href="header.php?page=view_tasks&action=delete&id=<?php echo $task['id']; ?>" class="btn-delete" onclick="return confirm('Delete this task?');">🗑 Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php

} elseif ($page === 'add_task') {
    ?>
    <div class="content">
        <h2>➕ Add Task</h2>

        <?php if ($taskSuccess): ?>
            <div class="success-message"><?php echo htmlspecialchars($taskSuccess); ?></div>
        <?php endif; ?>
        <?php if ($taskError): ?>
            <div class="error-message"><?php echo htmlspecialchars($taskError); ?></div>
        <?php endif; ?>

        <form method="POST" action="header.php?page=add_task" class="task-form">
            <div class="form-group">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <?php if ($role === 'admin'): ?>
                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select id="assigned_to" name="assigned_to">
                        <?php foreach ($allUsers as $u): ?>
                            <option value="<?php echo htmlspecialchars($u); ?>"><?php echo htmlspecialchars($u); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Assigned To</label>
                    <input type="text" value="<?php echo htmlspecialchars($username); ?> (you)" disabled>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">💾 Save Task</button>
            <button type="reset" class="btn-reset-form">Clear</button>
        </form>
    </div>
    <?php

} elseif ($page === 'about') {
    ?>
    <div class="content">
        <h2>ℹ️ About This Project</h2>
        <div class="about-container">
            <div class="about-section">
                <h3>Project Overview</h3>
                <p>The Task Management System is a role-based web application built for ITCC1023 - Web Systems and Technologies I. It allows administrators and students to manage tasks with different levels of access, using PHP sessions for data persistence without a database.</p>
            </div>
            <div class="about-section">
                <h3>Key Features</h3>
                <ul>
                    <li>Session-based login with role detection</li>
                    <li>Role-based page access (Admin / Student)</li>
                    <li>Task creation, completion, and deletion</li>
                    <li>Persistent task data across login sessions</li>
                    <li>Single-file routing through header.php</li>
                </ul>
            </div>
            <div class="about-section">
                <h3>Technologies Used</h3>
                <ul>
                    <li>PHP (Sessions, File Inclusion)</li>
                    <li>HTML5 &amp; CSS3</li>
                    <li>No external database — all data stored in $_SESSION</li>
                </ul>
            </div>
        </div>
    </div>
    <?php

} elseif ($page === 'team') {

    $team = [
        ['name' => 'Andrey',   'desc' => 'Luis Andrey A. Baluyot.', 'photo' => 'dre.jpg'],
        ['name' => 'Raye',    'desc' => 'Raye Lorence C. Cortez', 'photo' => 'ry.jpg'],
        ['name' => 'Russell',  'desc' => 'Russell Rain R. De Leon.', 'photo' => 'russ.jpg'],
        ['name' => 'Jericson', 'desc' => 'Jericson S. Camacho.', 'photo' => 'jeric.jpg'],
    ];
    ?>
    <div class="content">
        <h2>👥 Our Team</h2>
        <div class="team-grid">
            <?php foreach ($team as $member): ?>
                <div class="team-card">
                    <img class="member-photo" src="<?php echo htmlspecialchars($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                    <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="description"><?php echo htmlspecialchars($member['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

} elseif ($page === 'users') {

    $usersList = [
        'admin'    => 'Admin',
        'student1' => 'Student',
        'student2' => 'Student',
    ];
    ?>
    <div class="content">
        <h2>👥 Users</h2>
        <table class="task-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Total Tasks</th>
                    <th>Pending</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usersList as $uname => $urole):
                    $userTasks  = array_filter($_SESSION['tasks'], function ($t) use ($uname) { return $t['owner'] === $uname; });
                    $uTotal     = count($userTasks);
                    $uPending   = count(array_filter($userTasks, function ($t) { return $t['status'] === 'pending'; }));
                    $uCompleted = count(array_filter($userTasks, function ($t) { return $t['status'] === 'complete'; }));
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($uname); ?></td>
                        <td><span class="role-badge <?php echo $urole === 'Admin' ? 'admin' : 'student'; ?>"><?php echo $urole; ?></span></td>
                        <td><?php echo $uTotal; ?></td>
                        <td><?php echo $uPending; ?></td>
                        <td><?php echo $uCompleted; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

include 'footer.php';