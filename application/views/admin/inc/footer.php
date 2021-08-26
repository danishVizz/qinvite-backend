</div>
</div>
<footer class="sticky-footer bg-white"> 
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2020</span>
                    </div>
                </div>
            </footer>
</div>
            
</div>
            <!-- End of Main Content -->


        </div>
        <!-- End of Content Wrapper -->
            <!-- Footer -->
           
            <!-- End of Footer -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?=base_url()?>admin/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Details popup -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Nothing found!</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body text-dark">
                        <span>Data is no longer available</span>
                </div>
                <!-- <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?=base_url()?>admin/logout">Logout</a>
                </div> -->
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?=base_url();?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?=base_url();?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?=base_url();?>assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?=base_url();?>assets/js/sb-admin-2.min.js"></script>
    <script>
    $('body').on('change','#user-role',function() {

        let role = parseInt($(this).val());
        console.log(role);
        if( role == 4 || role == 5){
            $('#price_field').removeClass('d-none')
        }else{
            $('#price_field').addClass('d-none')
        }
    })
            $('body').on('click', '.get_details', function(){
                let id = $(this).attr('id');
                let path = $(this).attr('path');
                $.ajax({
                    url: `<?=base_url()?>admin/${path}`,
                    method: "POST",
                    data: {id:id},
                    dataType: 'json',
                    success: function(data){
                        let img;
                        // === Display package detail ===
                        if(path == "get_packageDetails"){
                            if(data.length > 0){
                                let packageType = (data[0].package_type == 0 ? "Default" : "Custom");
                                let promoCode = (!data[0].promo_code ? "--" : data[0].promo_code);
                                img = (!data[0].package_logo ? "1.png" : data[0].package_logo);
                                // Title
                                title = "Package Details";

                                // Content
                                html = `<div class="ajax"><div class="row align-items-center"><div class="col-sm-6"><h2 class="text-success">${data[0].package_name}</h2><h6 class="text-info">${packageType}</h6></div><div class="col-sm-6"> <img class="img-fluid w-50 d-block mx-auto" src="<?=base_url()?>images/package_img/${img}" alt=""></div></div><hr><div class="row"><div class="col-sm-12 mb-1"> <span class="font-weight-bold">No. of People: </span><span>${data[0].package_people}</span></div><div class="col-sm-12 mb-1"> <span class="font-weight-bold">Price: </span><span>${data[0].package_price}</span></div><div class="col-sm-12 mb-1"> <span class="font-weight-bold">Promocode: </span><span>${promoCode}</span></div></div></div>`;

                                // Injecting code in modal
                                $('#detailsModal .modal-title').html(title);
                                $('#detailsModal .modal-body').html(html);
                            }else{
                                $('#detailsModal .modal-title').html("Package Details");
                                $('#detailsModal .modal-body').html("No relevant data found");
                            }
                        }

                        // ===== Display user detail ====
                        else if(path == "get_userDetails"){
                            if(data.length > 0){
                                let role;
                                img = (!data[0].user_image ? "1.jpg" : data[0].user_image);
                                if(data[0].role == 0){
                                    role = "Admin";
                                }else if(data[0].role == 1){
                                    role = "Assistant";
                                }else if(data[0].role == 2){
                                    role = "Host";
                                }else if(data[0].role == 3){
                                    role = "Receptionist Admin";
                                }else if(data[0].role == 4){
                                    role = "Receptionist";
                                }

                                // Title
                                title = "User Details";

                                // Content
                                html = `<div class="ajax"><div class="row align-items-center"><div class="col-sm-6"><h2 class="text-dark">${data[0].first_name} ${data[0].last_name}</h2><h6 class="text-info">${role}</h6></div><div class="col-sm-6"> <img class="profile_img img-fluid rounded-circle w-50 d-block mx-auto" src="<?=base_url()?>images/user_img/${img}" alt=""></div></div><hr><div class="row"><div class="col-sm-12 mb-1"><span class="font-weight-bold">ID Card: </span><span>${data[0].username}</span></div><div class="col-sm-12 mb-1"><span class="font-weight-bold">Phone: </span><span>${data[0].phone}</span></div><div class="col-sm-12 mb-1"><span class="font-weight-bold">Email Address: </span><span>${data[0].email}</span></div><div class="col-sm-12 mb-1"><span class="font-weight-bold">Location: </span><span>${data[0].city}, ${data[0].country}</span></div></div></div>`;

                                // Injecting code in modal
                                $('#detailsModal .modal-title').html(title);
                                $('#detailsModal .modal-body').html(html);
                            }else{
                                $('#detailsModal .modal-title').html("User Details");
                                $('#detailsModal .modal-body').html("No relevant data found");
                            }

                                
                            
                        }

                        // ===== Display event detail ====
                        else if(path == "get_eventDetails"){
                            if(data.length > 0){
                                img = (!data[0].event_card ? "1.jpg" : data[0].event_card);
                                let eventStatus;
                                if(data[0].event_status == 0){
                                    eventStatus = "Pending";
                                }else if(data[0].event_status == 1){
                                    eventStatus = "Active";
                                }else if(data[0].event_status == 2){
                                    eventStatus = "Finished";
                                }

                                // Title
                                title = "Event Details";

                                // Content
                                html = `<div class="ajax"><div class="row align-items-center"><div class="col-sm-6"><h2 class="text-dark">${data[0].event_name}</h2><h6 class="text-info">${eventStatus}</h6></div><div class="col-sm-6"> <img class="profile_img img-fluid rounded w-50 d-block mx-auto" src="<?=base_url()?>images/event_card/${img}" alt=""></div></div><hr><div class="row"><div class="col-sm-12 mb-1"><span class="font-weight-bold">Address: </span><span>${data[0].event_address}</span></div><div class="col-sm-12 mb-1"><span class="font-weight-bold">Date: </span><span>${data[0].event_date}</span></div></div>`;

                                // Injecting code in modal
                                $('#detailsModal .modal-title').html(title);
                                $('#detailsModal .modal-body').html(html);
                            }else{
                                $('#detailsModal .modal-title').html("Event Details");
                                $('#detailsModal .modal-body').html("No relevant data found");
                            }

                        }

                        // ===== Display promo code detail ====
                        else if(path == "get_promocodeDetails"){
                            if(data.length > 0){
                                let eventStatus = (data[0].status == 0 ? "Active" : "Used")

                                // Title
                                title = "Promo Code Details";

                                // Content
                                html = `<div class="ajax"><div class="row align-items-center justify-content-center"><div class="col-sm-6 text-center"><h2 class="text-dark font-weight-bold">${data[0].code}</h2><h1 class="text-success font-weight-bold" style="font-size: 80px">${data[0].discount}%</h1> <span class="text-info">${eventStatus}</span></div></div></div>`;

                                // Injecting code in modal
                                $('#detailsModal .modal-title').html(title);
                                $('#detailsModal .modal-body').html(html);
                            }else{
                                $('#detailsModal .modal-title').html("Promo Code Details");
                                $('#detailsModal .modal-body').html("No relevant data found");
                            }
                        }
                    }
                });
            
               
            
            });
    </script>

</body>

</html>

