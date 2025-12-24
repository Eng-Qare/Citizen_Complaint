<?php
// Bootstrap navbar
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="/Citizen_Complaint/public/dashboard.php">City Complaints</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/Citizen_Complaint/public/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/Citizen_Complaint/public/complaint-submit.php">Submit Complaint</a></li>
        <li class="nav-item"><a class="nav-link" href="/Citizen_Complaint/public/complaint-track.php">Track Complaints</a></li>
        <li class="nav-item"><a class="nav-link" href="/Citizen_Complaint/public/user-list.php">Users</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/Citizen_Complaint/public/logout.php">Logout <i class="fa fa-sign-out-alt"></i></a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
