<?php
session_start();
include('includes/db.php');
include('includes/helpers.php');

if(!isset($_SESSION['isloggedin']) or ($_SESSION['isloggedin'] != true)) {
  header('Location: index.php');
}


if(isset($_GET['action'])) {
  if($_GET['action'] == 'signout') {
    unset($_SESSION['email']);
    unset($_SESSION['isloggedin']);
    session_destroy();
    header('Location: index.php');
  }
}


if(isset($_POST['reservation_btn'])) {
  $user_id = $_SESSION['user_id'];
  $room_id = $_POST['room_id'];
  $checkin = date_format(date_create($_POST['checkin']), "Y-m-d H:i:s");
  $checkout = date_format(date_create($_POST['checkout']), "Y-m-d H:i:s");
  $diff = date_diff(date_create($_POST['checkin']), date_create($_POST['checkout']));
  $days = (int)$diff->format('%a');
  $total = $_POST['price'] * $days;
  $status = 1;

  $sql = "INSERT INTO `reservations` (`user_id`,`room_id`,`checkin`,`checkout`, `total`, `status`) VALUES (?,?,?,?,?,?)";
  $stm = $pdo->prepare($sql);
  if($stm->execute([$user_id, $room_id, $checkin, $checkout, $total, $status])) {
    $sql = "UPDATE `rooms` SET `status`=? WHERE `id` =? ";
    $stm = $pdo->prepare($sql);
    $stm->execute([1, $room_id]);
    header('Location: reservations.php?room_id=' .$room_id .'&status=1');
  } else {
    header('Location: room-details.php?room_id='.$room_id);
  }
}


if(isset($_GET['room_id'])) {
  $sql = "SELECT * FROM `rooms` WHERE `id` = ?";
  $stm = $pdo->prepare($sql);
  if($stm->execute([$_GET['room_id']])) {
    $room = $stm->fetch(PDO::FETCH_ASSOC);

    if($room) {
      $services = get_services($_GET['room_id']);
      $media = get_media($_GET['room_id']);
    }
  }
} else {
  header('Location: rooms.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room details - Hotel Booking System</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/output.css">
</head>
<body>
    <!--
  This example requires updating your template:

  ```
  <html class="h-full bg-gray-100">
  <body class="h-full">
  ```
-->
<div class="min-h-full">
  <nav class="bg-gray-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center">
          <div class="shrink-0">
            <img class="size-8" src="./assets/images/logo.png" alt="Your Company">
          </div>
          <div class="hidden md:block">
            <div class="ml-10 flex items-baseline space-x-4">
              <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-gray-700 hover:text-white" -->
              <a href="dashboard.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Dashboard</a>
              <a href="rooms.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Rooms</a>
              <a href="reservations.php" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white">Reservations</a>
            </div>
          </div>
        </div>
        <div class="hidden md:block">
          <div class="ml-4 flex items-center md:ml-6">

            <!-- Profile dropdown -->
            <div class="relative ml-3">
              <div class="group ">
                <button type="button" class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                  <span class="absolute -inset-1.5"></span>
                  <span class="sr-only">Open user menu</span>
                  <img class="size-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                </button>
                <div class="hidden group-hover:block absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                    <!-- Active: "bg-gray-100 outline-none", Not Active: "" -->
                    <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
                    <a href="?action=signout" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="user-menu-item-2">Sign out</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="-mr-2 flex md:hidden">
          <!-- Mobile menu button -->
          <button type="button" class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800" aria-controls="mobile-menu" aria-expanded="false">
            <span class="absolute -inset-0.5"></span>
            <span class="sr-only">Open main menu</span>
            <!-- Menu open: "hidden", Menu closed: "block" -->
            <svg class="block size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <!-- Menu open: "block", Menu closed: "hidden" -->
            <svg class="hidden size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </nav>

  <header class="bg-white shadow">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold tracking-tight text-gray-900">Room #<?= $room['number'] ?></h1>
    </div>
  </header>
  <main>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">


    <!-- Media -->
    <?php if(count($media) > 0): ?>
    <section class="bg-white dark:bg-gray-900">
        <div class="container pb-8 mx-auto">

            <div class="grid gap-8 mt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              <?php foreach($media as $item): ?>
                <div class="w-full max-w-xs text-center border border-gray-300 rounded-lg">
                    <img class="object-cover object-center w-full h-48 mx-auto rounded-lg" src="<?= $item['filename'] ?>" alt="Room #<?= $room['number'] ?>" />
                </div>
              <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <!-- Title / Price / Description -->
    <div class="">
        <div class="flex items-center justify-end">
            <span class="px-3 py-1 text-sm font-bold text-gray-100 transition-colors duration-300 transform bg-gray-600 rounded" tabindex="0" role="button"><?= number_format($room['price'], 2, ".", "") ?> &euro;</span>
        </div>

        <div class="mt-2">
            <h2 class="text-3xl font-bold text-gray-700 dark:text-white" tabindex="0" role="link">Room <?= $room['number'] ?></h2>
            <p class="mt-6 text-gray-600 dark:text-gray-300"><?= $room['description'] ?></p>
        </div>
    </div>


    <!-- Room Services -->
    <?php if(count($services) > 0): ?>
    <section class="bg-white my-12 dark:bg-gray-900">
        <div class="container px-6 py-12 mx-auto">

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
                <?php foreach($services as $service): ?>
                <div class="flex flex-col items-center">
                    <?= $service['icon'] ?>
                    <h1 class="mt-4 text-xl font-semibold text-gray-800 dark:text-white"><?= $service['name'] ?></h1>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Reservation -->
    <?php if($room['status'] == 0): ?>
    <section class=" bg-white rounded-md">
    <h2 class="text-lg font-semibold text-gray-700 capitalize dark:text-white">Reservation</h2>
      <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
          <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-2">
              <div>
                  <label for="checkin" class="block text-sm text-gray-500 dark:text-gray-300">Check in</label>

                  <input type="datetime-local" name="checkin" placeholder="Check in" class="block  mt-2 w-full placeholder-gray-400/70 dark:placeholder-gray-500 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-gray-700 focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-blue-300" id="checkin"/>
              </div>

              <div>
                  <label for="checkout" class="block text-sm text-gray-500 dark:text-gray-300">Check out</label>

                  <input type="datetime-local" name="checkout" placeholder="Check out" class="block  mt-2 w-full placeholder-gray-400/70 dark:placeholder-gray-500 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-gray-700 focus:border-blue-400 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-40 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-blue-300" id="checkout" />
              </div>
          </div>

          <input type="hidden" name="room_id" value="<?= $room['id'] ?>" />
          <input type="hidden" name="price" value="<?= $room['price'] ?>" />

          <div class="flex justify-end mt-6">
              <button type="submit" name="reservation_btn" class="px-8 py-2.5 leading-5 text-white transition-colors duration-300 transform bg-gray-700 rounded-md hover:bg-gray-600 focus:outline-none focus:bg-gray-600">Submit</button>
          </div>
      </form>
    </section>
    <?php else: ?>
      <div class="mt-12 flex w-full max-w-sm overflow-hidden bg-white rounded-lg shadow-md dark:bg-gray-800">
          <div class="flex items-center justify-center w-12 bg-blue-500">
              <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                  <path d="M20 3.33331C10.8 3.33331 3.33337 10.8 3.33337 20C3.33337 29.2 10.8 36.6666 20 36.6666C29.2 36.6666 36.6667 29.2 36.6667 20C36.6667 10.8 29.2 3.33331 20 3.33331ZM21.6667 28.3333H18.3334V25H21.6667V28.3333ZM21.6667 21.6666H18.3334V11.6666H21.6667V21.6666Z" />
              </svg>
          </div>

          <div class="px-4 py-2 -mx-3">
              <div class="mx-3">
                  <span class="font-semibold text-blue-500 dark:text-blue-400">Info</span>
                  <p class="text-sm text-gray-600 dark:text-gray-200">Sorry! Room is booked.</p>
              </div>
          </div>
      </div>
    <?php endif; ?>



    </div>
  </main>
</div>

</body>
</html>