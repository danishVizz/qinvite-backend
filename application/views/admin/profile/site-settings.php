<?php
    $this->load->view('admin/inc/header');
    $this->load->view('admin/inc/sidebar');
?>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Site Settings</h1>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">Site settings</div>
            <div class="card-body">
                <?php 
                    if($this->session->flashdata("errors")){
                        echo '<div class="alert alert-danger text-center">'.$this->session->flashdata("errors").'</div>';
                    }elseif($this->session->flashdata('success')){
                        echo '<div class="alert alert-success text-center">'.$this->session->flashdata("success").'</div>';
                    }
                  
                ?>
                <form method="post" action="" enctype="multipart/form-data" id="setting_form">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputsite_title">Title</label>
                            <input class="form-control" name="site_title" id="inputsite_title" type="text" required value="<?=$site_title?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputsite_desc">Description</label>
                            <input class="form-control" name="site_desc" id="inputsite_desc" type="text" required value="<?=$site_desc?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputmeta_tags">Meta tags</label>
                            <input class="form-control" name="meta_tags" id="inputmeta_tags" type="text" required value="<?=$meta_tags?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputmeta_desc">Meta description</label>
                            <textarea class="form-control" name="meta_desc" id="inputmeta_desc" required><?=$meta_desc?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputreceptionist_price">Receptionist Price (QR)</label>
                            <input class="form-control" name="receptionist_price" id="inputreceptionist_price" type="number" min=1 required value="<?=$receptionist_price?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputprice_per_invite">Price per Invitation (QR)</label>
                            <input class="form-control" name="price_per_invite" id="inputprice_per_invite" type="number" min="1.00" step=".01" required value="<?=$price_per_invite?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputsadad_id">Sadad ID</label>
                            <textarea class="form-control" name="sadad_id" id="inputsadad_id" required><?=$sadad_id?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputapi_key">Sadad API key</label>
                            <textarea class="form-control" name="api_key" id="inputapi_key" required><?=$api_key?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small mb-1" for="inputdomain">Sadad Domain</label>
                            <textarea class="form-control" name="domain" id="inputdomain" required><?=$domain?></textarea>
                        </div>
                    </div>

                    
                    
                    <div class="border chat_api_box p-3 mb-1 col-md-6">
                        <?php for($i = 0; $i < count($chat_api_instance_id); $i++) : ?>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="small mb-1" for="inputchat_api_instance_id">Chat API (Instance ID)</label>
                                <textarea class="form-control" name="chat_api_instance_id[]" id="inputchat_api_instance_id" required><?=$chat_api_instance_id[$i]?></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="small mb-1" for="inputchat_api_token">Chat API (Token)</label>
                                <textarea class="form-control" name="chat_api_token[]" id="inputchat_api_token" required><?=$chat_api_token[$i]?></textarea>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <div class="form-row mb-5">
                    <div class="col-md-6 text-right">
                        <button class="btn btn-primary add_api_box" type="button">+</button>
                        <button class="btn btn-danger remove_api_box" type="button">-</button>
                    </div>
                    </div>
                    
                    
                    <!-- Save changes button-->
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    $this->load->view('admin/inc/footer');
?>

<script>
    $(document).ready(function(){
        
        $('body').on('click','.add_api_box', function(){
            console.log('work');

            $html = '<div class="form-row"><div class="form-group col-md-12"> <label class="small mb-1" for="inputchat_api_instance_id">Chat API (Instance ID)</label><textarea class="form-control" name="chat_api_instance_id[]" id="inputchat_api_instance_id" required></textarea></div></div><div class="form-row"><div class="form-group col-md-12"> <label class="small mb-1" for="inputchat_api_token">Chat API (Token)</label><textarea class="form-control" name="chat_api_token[]" id="inputchat_api_token" required></textarea></div></div>';

            $('.chat_api_box').append($html);
        })

        $('body').on('click', '.remove_api_box', function(){
            $('.chat_api_box').children().last().remove();
            $('.chat_api_box').children().last().remove();
        })
    })
</script>