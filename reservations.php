<?php
session_start();
include('includes/db.php');

if(!isset($_SESSION['isloggedin']) || $_SESSION['isloggedin'] !== true) {
  header('location:index.php');
  exit;
}

if(!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
  header('location:index.php');
  exit;
}

if(isset($_GET['action'])) {
  if($_GET['action'] == 'signout') {
    unset($_SESSION['email'], $_SESSION['isloggedin'], $_SESSION['role'], $_SESSION['user_id']);
    session_destroy();
    header('location:index.php');
    exit;
  } elseif($_GET['action'] == 'delete' && $_SESSION['role'] === 'admin') {
    $rid = intval($_GET['reservation_id']);
    $sql = "DELETE FROM `reservations` WHERE `id` = ? LIMIT 1";
    $stm = $pdo->prepare($sql);
    $stm->execute([$rid]);
    header('location: reservations.php');
    exit;
  }
}

$res_sql = "SELECT 
  reservations.`id` AS `rid`, 
  users.*, 
  `rooms`.`id` AS `room_id`, 
  `rooms`.`price`, 
  `rooms`.`number`, 
  reservations.checkin, 
  reservations.checkout, 
  reservations.total, 
  reservations.payment_type, 
  reservations.status
FROM `reservations` 
INNER JOIN `rooms` ON `reservations`.`room_id` = `rooms`.`id` 
INNER JOIN `users` ON `reservations`.`user_id` = `users`.`id`";

$params = [];
if($_SESSION['role'] !== 'admin') {
  // Non-admin users see only their own reservations
  $res_sql .= " WHERE `reservations`.`user_id` = ?";
  $params[] = $_SESSION["user_id"];
}

$stm = $pdo->prepare($res_sql);
$stm->execute($params);
$reservations = $stm->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reservations - Hotel Booking System</title>
  <link rel="stylesheet" href="./assets/css/output.css" />
</head>
<body>

<div class="min-h-full">
  <nav class="bg-gray-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center">
          <div class="shrink-0">
            <span class='font-bold text-white'>LOGO</span>
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <a href="dashboard.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Dashboard</a>
              <a href="rooms.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Rooms</a>
              <a href="reservations.php" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Reservations</a>
            </div>
          </div>
        </div>
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">
            <div class="relative ml-3">
              <div class="group">
                <button type="button" class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">Open user menu</span>
                  <img class="size-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User" />
                </button>
                <div class="hidden group-hover:block absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                  <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">Your Profile</a>
                  <a href="?action=signout" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">Sign out</a>    
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <button type="button" class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" aria-controls="mobile-menu" aria-expanded="false">
            <span class="absolute -inset-0.5"></span>
            <span class="sr-only">Open main menu</span>
            <svg class="block size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <svg class="hidden size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </nav>

  <header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold tracking-tight text-gray-900">Reservations</h1>
    </div>
  </header>

  <main>
    <div class="mx-auto max-w-7xl px-4">

      <section class="container px-4 mx-auto">
        <div class="flex flex-col mt-6">
          <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
              <div class="overflow-hidden border border-gray-200 dark:border-gray-700 md:rounded-lg">
                <?php if(count($reservations) > 0) : ?>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                      <th scope="col" class="py-3.5 px-4 text-sm font-normal text-left rtl:text-right text-gray-500 dark:text-gray-400">Room</th>
                      <th scope="col" class="px-4 py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 dark:text-gray-400">Checkin / Checkout</th>
                      <th scope="col" class="px-4 py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 dark:text-gray-400">Total</th>
                      <th scope="col" class="px-12 py-3.5 text-sm font-normal text-left rtl:text-right text-gray-500 dark:text-gray-400">Status</th>
                      <th scope="col" class="relative py-3.5 px-4"></th>
                    </tr>
                  </thead>

                  <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-900">
                    <?php foreach($reservations as $reservation): ?>
                      <tr>
                        <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                          <div>
                            <h2 class="font-medium text-gray-800 dark:text-white">Room</h2>
                            <p class="text-sm font-normal text-gray-600 dark:text-gray-400"><?=htmlspecialchars($reservation['number']) ?></p>
                          </div>
                        </td>
                        <td class="px-4 py-4 text-sm whitespace-nowrap">
                          <div>
                            <h4 class="text-gray-700 dark:text-gray-200"><?=htmlspecialchars($reservation['checkin']) ?></h4>
                            <p class="text-gray-500 dark:text-gray-400"><?=htmlspecialchars($reservation['checkout']) ?></p>
                          </div>
                        </td>
                        <td class="px-4 py-4 text-sm whitespace-nowrap">
                          <div>
                            <h4 class="text-gray-700 dark:text-gray-200"><?=htmlspecialchars($reservation['total']) ?> &euro;</h4>
                            <p class="text-gray-500 dark:text-gray-400"><?=htmlspecialchars($reservation['payment_type']) ?></p>
                          </div>
                        </td>
                        <td class="px-12 py-4 text-sm font-medium whitespace-nowrap">
                          <div class="inline px-3 py-1 text-sm font-normal rounded-full text-emerald-500 gap-x-2 bg-emerald-100/60 dark:bg-gray-800">
                            <?=htmlspecialchars($reservation['status']) ?>
                          </div>
                        </td>
                        <td class="px-4 py-4 text-sm whitespace-nowrap">
                          <?php if($reservation['role'] == 'client'): ?>
                            <a href="room-details.php?room_id=<?=urlencode($reservation['room_id']) ?>" class="px-1 py-1 text-gray-500 transition-colors duration-200 rounded-lg dark:text-gray-300 hover:bg-gray-100">Room details</a>
                          <?php else: ?>
                            <a href="?action=delete&reservation_id=<?=urlencode($reservation['rid']) ?>" class="px-1 py-1 text-gray-500 transition-colors duration-200 rounded-lg dark:text-gray-300 hover:bg-gray-100" onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php else: ?>
                  <p class="p-4">0 Reservations</p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>
</div>

</body>
</html>
