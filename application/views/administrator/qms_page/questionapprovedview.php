
<h1 class="heading">Manage Questions (Approved)</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">
        
        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/question/filterapprove', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_question" id="filter_question" value="<?php echo set_value('filter_question', $this->form_data->filter_question); ?>" placeholder="Question" class="input-medium" />
                <?php //echo form_dropdown('filter_category', $this->cat_list_filter, $this->form_data->filter_category, 'id="filter_category" class="chosen-select"'); ?>
                <select name="category_id" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                <select name="sub_cat_parent"  id="lf_sub_cat_parent"  class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                <select name="sub_two_cat_parent" id="lf_sub_two_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                <select name="sub_three_cat_parent" id="lf_sub_three_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                <select name="sub_four_cat_parent" id="lf_sub_four_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                <?php echo form_dropdown('filter_type', $this->type_list_filter, $this->form_data->filter_type, 'id="filter_type" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_expired', $this->expired_list_filter, $this->form_data->filter_expired, 'id="filter_expired" class="chosen-select"'); ?>
                &nbsp;
                <span class="btn-group">
                    <input type="submit" value="Filter" class="btn" />
                    <?php if (count($filter) > 1): ?>
                    <button type="submit" name="filter_clear" value="Clear" title="Clear Filter" class="btn"><i class="icon-remove"></i></button>
                    <?php endif; ?>
                </span>

            <?php echo form_close(); ?>
            </div>
            <div class="span6 right">
                
                <?php echo $pagin_links; ?>

            </div>
        </div>

        <?php echo form_open('administrator/question/change_all_status_approve', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

        <?php echo $records_table; ?>

        <div style="margin-top:20px; float:right;">

         
         <button type="submit" name="reject_all" value="Reject Selected Questions" title="Reject All" class="btn">Reject Selected Questions</button>
     </div>
      <?php echo form_close(); ?>
        <div class="row control-row control-row-bottom">
            <div class="span6 left">&nbsp;</div>
            <div class="span6 right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        //approve_ques
        
        $("#aprall").click(function () {
          if(document.getElementById("aprall").checked==true)
          {
               //alert('checked');
              $('input[type="checkbox"]').prop('checked', true);
          }
          else
          {
                //alert('unchecked');
             $('input[type="checkbox"]').prop('checked', false);
          }
          //$(".aprall")
          
        });
    });
</script>
<style type="text/css">
    .mark { padding: 2px 5px; cursor: pointer; color: #0088CC; border-bottom: 1px solid #0088CC; }
    .mark:hover { color: #ffffff; background: #0088CC; border-bottom: none; }
</style>


<script type="text/javascript">
  //alert(<?php echo $this->form_data->filter_category; ?>);
    function loadCategory()
    {
        var getCurrentID=0;
        <?php 
        if(isset($this->form_data->filter_category))
        {
            if($this->form_data->filter_category>0)
            {
                ?>
                getCurrentID="<?php echo $this->form_data->filter_category; ?>";
                <?php
            }
        }
        ?>
        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("Counter Display Status : "+data);

                var htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Category</option>';
                    $.each(data,function(key,row){
                        if(row.id==getCurrentID)
                        {
                            htmlString +='<option selected="selected" value="'+row.id+'">'+row.cat_name+'</option>';
                        }
                        else
                        {
                            htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                        }
                    });
                }

                $("select[name=category_id]").html(htmlString).chosen();
                $('select[name=category_id]').trigger("liszt:updated");

                if(getCurrentID>0)
                {
                    loadSubCategory(getCurrentID);
                }
            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubCategory(cid)
    {
        var getCurrentID=0;
        <?php 
        if(isset($this->form_data->filter_sub_cat_parent))
        {
            if($this->form_data->filter_sub_cat_parent>0)
            {
                ?>
                getCurrentID="<?php echo $this->form_data->filter_sub_cat_parent; ?>";
                <?php
            }
        }
        ?>

        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            //htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                            if(row.id==getCurrentID)
                            {
                                htmlString +='<option selected="selected" value="'+row.id+'">'+row.cat_name+'</option>';
                            }
                            else
                            {
                                htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                            }
                        }
                        
                    });
                }

                $("select[name=sub_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_cat_parent]').trigger("liszt:updated");

                if(getCurrentID>0)
                {
                    $("select[name=sub_cat_parent]").change();
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubTwoCategory(cid,SubCat)
    {


        var getCurrentID=0;
        <?php 
        if(isset($this->form_data->filter_sub_two_cat_parent))
        {
            if($this->form_data->filter_sub_two_cat_parent>0)
            {
                ?>
                getCurrentID="<?php echo $this->form_data->filter_sub_two_cat_parent; ?>";
                <?php
            }
        }
        ?>

        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_two_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 2 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.id==getCurrentID)
                                {
                                    htmlString +='<option selected="selected" value="'+row.id+'">'+row.cat_name+'</option>';
                                }
                                else
                                {
                                    htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                }
                            }
                        }
                        
                    });
                }

                console.log(htmlString);

                $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

                if(getCurrentID>0)
                {
                    $("select[name=sub_two_cat_parent]").change();
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubThreeCategory(cid,SubCat,SubTwoCat)
    {


        var getCurrentID=0;
        <?php 
        if(isset($this->form_data->filter_sub_three_cat_parent))
        {
            if($this->form_data->filter_sub_three_cat_parent>0)
            {
                ?>
                getCurrentID="<?php echo $this->form_data->filter_sub_three_cat_parent; ?>";
                <?php
            }
        }
        ?>

        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_three_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 3 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.sub_two_cat_parent==SubTwoCat)
                                {
                                    if(row.id==getCurrentID)
                                    {
                                        htmlString +='<option selected="selected" value="'+row.id+'">'+row.cat_name+'</option>';
                                    }
                                    else
                                    {
                                        htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                    }
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

                if(getCurrentID>0)
                {
                    $("select[name=sub_three_cat_parent]").change();
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubFourCategory(cid,SubCat,SubTwoCat,SubThreeCat)
    {


        
        var getCurrentID=0;
        <?php 
        if(isset($this->form_data->filter_sub_four_cat_parent))
        {
            if($this->form_data->filter_sub_four_cat_parent>0)
            {
                ?>
                getCurrentID="<?php echo $this->form_data->filter_sub_four_cat_parent; ?>";
                <?php
            }
        }
        ?>

        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_four_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_four_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 4 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.sub_two_cat_parent==SubTwoCat)
                                {
                                    if(row.sub_three_cat_parent==SubThreeCat)
                                    {
                                          if(row.id==getCurrentID)
                                          {
                                              htmlString +='<option selected="selected" value="'+row.id+'">'+row.cat_name+'</option>';
                                          }
                                          else
                                          {
                                              htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                          }
                                    }
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_four_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");


            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    $(document).ready(function(){
        
        loadCategory();
        

        $("select[name=category_id]").change(function(){
            var cat_parent=$(this).val();
            if(cat_parent.length>0)
            {
                loadSubCategory(cat_parent);
            }
        });

      
        $("#lf_sub_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            //console.log(cat_parent);
            //return false;
            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$(this).val();
            if(sub_cat_parent.length>0)
            {
                loadSubTwoCategory(cat_parent,sub_cat_parent);
            }
            
        });
        
        $("#lf_sub_two_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$("#lf_sub_cat_parent").val();

            if(sub_cat_parent.length==0)
            {
                alert("Please select a Sub Category.");
                return false;
            }

            var sub_two_cat_parent=$(this).val();
            if(sub_two_cat_parent.length>0)
            {
                loadSubThreeCategory(cat_parent,sub_cat_parent,sub_two_cat_parent);
            }
            
        });

        $("#lf_sub_three_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$("#lf_sub_cat_parent").val();

            if(sub_cat_parent.length==0)
            {
                alert("Please select a Sub Category.");
                return false;
            }

            var sub_two_cat_parent=$("#lf_sub_two_cat_parent").val();

            if(sub_two_cat_parent.length==0)
            {
                alert("Please select a Sub 2 Category.");
                return false;
            }

            var sub_three_cat_parent=$(this).val();
            if(sub_three_cat_parent.length>0)
            {
                loadSubFourCategory(cat_parent,sub_cat_parent,sub_two_cat_parent,sub_three_cat_parent);
            }
            
        });
       
        
    });

   

</script>