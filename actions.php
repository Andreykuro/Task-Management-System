<?php
// ===================== task actions + add_task submit + announcements =====================
// yung mga session-mutation / redirect logic 
// note: header.php lang dapat tumatawag dito (require 'actions.php';), pagkatapos ng auth/valid-page checks

// view, complete delete actions fuh yeah - filters too btw


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

    $redirectBack = 'header.php?page=view_tasks';
    if ($viewTasksStatusFilter !== 'all') {
        $redirectBack .= '&status_filter=' . urlencode($viewTasksStatusFilter);
    }
    if ($viewTasksUserFilter !== 'all') {
        $redirectBack .= '&filter_user=' . urlencode($viewTasksUserFilter);
    }
    header('Location: ' . $redirectBack);
    exit();
}

// ----- add_task: form submission, ts where u push task type shi -----
if ($page === 'add_task' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dueDate     = trim($_POST['due_date'] ?? '');
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
            'due_date'     => $dueDate, // pwede blank, optional lang siya
            'date_created' => date('Y-m-d H:i'),
        ];
        $_SESSION['next_task_id']++;
        $taskSuccess = 'Task added successfully!';
    }
}

// ----- dashboard: class announcements (stream), only admins can modify ts -----
if ($page === 'dashboard' && $role === 'admin' && isset($_GET['delete_announcement'])) {
    $delId = (int) $_GET['delete_announcement'];
    $_SESSION['announcements'] = array_values(array_filter($_SESSION['announcements'], function ($a) use ($delId) {
        return $a['id'] !== $delId;
    }));
    header('Location: header.php?page=dashboard');
    exit();
}

$announcementSuccess = '';
if ($page === 'dashboard' && $role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_message'])) {
    $announcementMsg = trim($_POST['announcement_message']);
    if ($announcementMsg !== '') {
        $_SESSION['announcements'][] = [
            'id'          => $_SESSION['next_announcement_id'],
            'message'     => $announcementMsg,
            'posted_by'   => $username,
            'date_posted' => date('Y-m-d H:i'),
        ];
        $_SESSION['next_announcement_id']++;
        $announcementSuccess = 'Announcement posted!';
    }
} 
?>