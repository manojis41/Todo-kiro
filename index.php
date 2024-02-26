<?php

// Some stuff
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Twig stuff
require_once __DIR__ . '/vendor/autoload.php';
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);


$servername = "localhost";
$username = "root";
$password = "";
$database = "todo";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$route = $_SERVER['REQUEST_URI'];
$tasks = [];

function render_page($conn, $twig, $page)
{
    $sql = "SELECT * FROM $page ORDER BY id DESC";
    $result = $conn->query($sql);
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo $twig->render($page . '.html.twig', ['tasks' => $tasks]);
}
$next_page = ['current' => 'completed', 'completed' => 'current'];
function check($conn, $twig, $next_page, $page)
{
    $destination = $next_page[$page];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $task_id = htmlspecialchars($_POST['boxid']);

        $sql1 = ("INSERT INTO $destination SELECT * FROM $page WHERE id={$task_id};");
        $sql2 = ("DELETE FROM $page WHERE id={$task_id};");
        $conn->query($sql1);
        $conn->query($sql2);
    }
    render_page($conn, $twig, $page);
}
switch ($route) {
    case '/todo/':
        $page = 'current';
        render_page($conn, $twig, $page);

        break;

    case '/todo/completed/':
        $page = 'completed';
        render_page($conn, $twig, $page);
        break;

    case '/todo/add':
        $page = 'current';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $task = htmlspecialchars($_POST['task']);

            $sql = ("INSERT INTO current (task) VALUES('$task')");
            $conn->query($sql);
        }
        render_page($conn, $twig, $page);
        break;

    case '/todo/done':
        $page = 'completed';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $page = 'current';
            check($conn, $twig, $next_page, $page);
        } else
            render_page($conn, $twig, $page);
        break;


    case '/todo/ongoing':
        $page = 'current';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $page = 'completed';
            check($conn, $twig, $next_page, $page);
        } else
            render_page($conn, $twig, $page);
        break;

    case '/todo/remove':
        $page;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $page = htmlspecialchars($_POST['type']);
            $task_id = htmlspecialchars($_POST['boxid']);

            $sql = "DELETE FROM {$page} WHERE id={$task_id}";
            $conn->query($sql);
        }
        render_page($conn, $twig, $page);
        break;

    case '/todo/okay':
        $page;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $page = htmlspecialchars($_POST['type']);
            $task_id = htmlspecialchars($_POST['boxid']);
            $updated_text = htmlspecialchars($_POST['updatedTask']);

            if ($page == "completed" or $page == "current") {
                $sql = "UPDATE {$page} SET task = '{$updated_text}' WHERE id = '{$task_id}'";
            }
            $conn->query($sql);

            if ($page == 'completed')
                render_page($conn, $twig, $page);
            break;
        }
        render_page($conn, $twig, "current");
        break;

    default:
        echo ("Something went wrong <br> Please get back to homepage <a href='/todo/'>Current Tasks></a>");
        break;
}