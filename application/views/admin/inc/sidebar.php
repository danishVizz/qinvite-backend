<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url(); ?>admin/dashboard">
    <div class="sidebar-brand-text mx-3">Qinvite</sup></div>
</a>

<!-- Divider -->
<hr class="sidebar-divider my-0">

<!-- Nav Item - Dashboard -->
<li class="nav-item active">
    <a class="nav-link" href="<?= base_url(); ?>admin/dashboard">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span></a>
</li>

<?php if($this->session->userdata('user_role') !=3): ?>
<!-- Nav Item - Pages Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
        aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-users"></i>
        <span>Users</span>
    </a>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?= base_url(); ?>admin/users">Users</a>
            <a class="collapse-item" href="<?= base_url(); ?>admin/add_user">Add user</a>
        </div>
    </div>
</li>




<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseEvents"
        aria-expanded="true" aria-controls="collapseEvents">
        <i class="fas fa-calendar-week"></i>
        <span>Events</span>
    </a>
    <div id="collapseEvents" class="collapse" aria-labelledby="headingEvents" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?=base_url()?>admin/events">Events</a>
            <!-- <a class="collapse-item" href="<?=base_url()?>admin/add_event">Add Event</a> -->
        </div>
    </div>
</li>


<!-- Nav Item - Packages Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePackages"
        aria-expanded="true" aria-controls="collapsePackages">
        <i class="fas fa-medal"></i>
        <span>Packages</span>
    </a>
    <div id="collapsePackages" class="collapse" aria-labelledby="headingPackages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?=base_url()?>admin/packages">Packages</a>
            <a class="collapse-item" href="<?=base_url()?>admin/add_package">Add Packages</a>
        </div>
    </div>
</li>

<!-- Nav Item - Promo codes Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePromocodes"
        aria-expanded="true" aria-controls="collapsePromocodes">
        <i class="fas fa-certificate"></i>
        <span>Promo codes</span>
    </a>
    <div id="collapsePromocodes" class="collapse" aria-labelledby="headingPromocodes" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?=base_url()?>admin/promocodes">Promo Codes</a>
            <a class="collapse-item" href="<?=base_url()?>admin/add_promocode">Add Promo codes</a>
        </div>
    </div>
</li>
<!-- Nav Item - Charts -->
<li class="nav-item">
    <a class="nav-link" href="charts.html">
        <i class="fas fa-fw fa-chart-area"></i>
        <span>Designers</span>
    </a>
</li>

<!-- Nav Item - Promo codes Collapse Menu  Categories -->
<!-- <li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCategories"
        aria-expanded="true" aria-controls="collapseCategories">
        <i class="fas fa-clipboard-list"></i>
        <span>Categories</span>
    </a>
    <div id="collapseCategories" class="collapse" aria-labelledby="headingCategories" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?=base_url()?>admin/categories">Categories</a>
            <a class="collapse-item" href="<?=base_url()?>admin/add_category">Add Category</a>
        </div>
    </div>
</li> -->
<?php endif; ?>
<?php if($this->session->userdata('user_role') == 3): ?>
<!-- Nav Item - Pages Collapse Menu -->
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReceptionist"
        aria-expanded="true" aria-controls="collapseReceptionist">
        <i class="fas fa-users"></i>
        <span>Receptionist</span>
    </a>
    <div id="collapseReceptionist" class="collapse" aria-labelledby="headingReceptionist" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?= base_url(); ?>admin/receptionists">Receptionists</a>
            <a class="collapse-item" href="<?= base_url(); ?>admin/add_receptionist">Add Receptionist</a>
        </div>
    </div>
</li>
<?php endif; ?>



<!-- Nav Item - Tables -->
<li class="nav-item d-none">
    <a class="nav-link" href="tables.html">
        <i class="fas fa-fw fa-table"></i>
        <span>Tables</span></a>
</li>

<!-- Divider -->
<hr class="sidebar-divider d-none d-md-block">

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div>

</ul>
<!-- End of Sidebar -->
<div id="content-wrapper" class="d-flex flex-column">
 <!-- Main Content -->
 <div id="content">

<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <!-- d-sm-inline-block form-inline -->
    <form
        class="d-none mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    
    <?php
            $user = get_curr_user();
            if($user){
                $fname = $user->first_name;
                $lname = $user->last_name;
                $user_image = $user->user_image;
                $role = $user->role;
                if($role == 0){
                    $role = 'Admin';
                }else if($role == 1){
                    $role = "Admin Assistant";
                }else if($role == 3){
                    $role = 'Receptionist admin';
                }else{
                    $role = "--";
                }
            }
        ?>
    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item">
            <span class="text-info"><?= $role ?></span>
        </li>
        <div class="topbar-divider d-none d-sm-block"></div>
        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?=$fname?> <?=$lname?></span>
                <?php
                    if($user_image){
                    
                        echo '<img class="img-profile rounded-circle" src="'.base_url().'images/user_img/'.$user_image.'" alt="Profile Image">';
                     
                    }else{
                        echo '<img class="img-profile rounded-circle" src="'.base_url().'assets/img/undraw_profile.svg">';
                    }
                ?>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?=base_url()?>admin/edit_profile">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <?php if($this->session->userdata('user_role') != 3): ?>
                <a class="dropdown-item" href="<?=base_url()?>admin/settings">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->
<div class="container-fluid">