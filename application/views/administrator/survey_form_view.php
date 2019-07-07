

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Create New'; ?> Survey</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/survey/add_survey', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/survey/update_survey', array('class' => 'form-horizontal')); ?>
<?php endif; ?>


    <?php 
    //print_r_pre($modify_question);
    ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="survey_title">Survey Title</label>
            <div class="controls">
                <input type="text" name="survey_title" id="survey_title" value="<?php echo set_value('survey_title', $this->form_data->survey_title); ?>" class="input-xxlarge" />
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="survey_description">Short Description</label>
            <div class="controls">
                <textarea name="survey_description" id="survey_description" rows="4" cols="30" class="input-xxlarge"><?php echo set_value('survey_description', $this->form_data->survey_description); ?></textarea>
            </div>
        </div>
        
       

    

    <div class="control-group">
            <label class="control-label" for="cat_parent">Select Category</label>
            <div class="controls">
                <select name="category_id" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="sub_cat_parent">Select Sub Category</label>
            <div class="controls">
                <select name="sub_cat_parent"  id="lf_sub_cat_parent"  class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"  for="sub_two_cat_parents">Select Sub 2 Category</label>
            <div class="controls">
                <select name="sub_two_cat_parent" id="lf_sub_two_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"  for="sub_three_cat_parent">Select Sub 3 Category</label>
            <div class="controls">
                <select name="sub_three_cat_parent" id="lf_sub_three_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"  for="sub_three_cat_parent">Select Sub 4 Category</label>
            <div class="controls">
                <select name="sub_four_cat_parent" id="lf_sub_four_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
                
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"  for="sub_three_cat_parent">&nbsp;</label>
            <div class="controls">
                <button type="button" class="btn btn-info loadQuestion"><i class="icon-search"></i> Load Question</button>
            </div>
        </div>




    <div class="span10">
        <table class="table " id="ques_tbl" style="margin-bottom: 20px;">
            <thead>
            <tr>
                <th width="20">SL</th>
                <th width="500">Questions</th>
                <th>Select Question</th>
            </tr>
            </thead>
            <tbody id="ques_tbl_body">
            </tbody>
        </table>

        <div class="" style="margin-top:2%;margin-bottom: 5%">
            <input onclick="AddAllSelectedQuestion()" type="button" value="Add All Question" class="btn btn-success btn-sm" />&nbsp;&nbsp;
        </div>

    </div>



    <div class="control-group formSep">

    </div>

    <div style="margin-top:2%;">
        <h3>Added Question List:</h3>
    </div>


    <div class="span10" style="overflow-x: hidden;">
        <table class="table  table-striped" id="added_ques_tbl" style="margin-bottom: 0;">
            <thead>
            <tr>
                <th width="20">SL</th>
                <th width="500">Questions</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="added_ques_tbl_body">
                  </tbody>
        </table>

        <div class="control-group formSep">

        </div>


    </div>


        <div class="control-group formSep">
            <label class="control-label" for="survey_status1">Survey Status</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="survey_status" id="survey_status1" value="open" <?php echo set_radio('survey_status', 'open', $this->form_data->survey_status == 'open'); ?> /> Open
                </label>
                <label class="radio inline">
                    <input type="radio" name="survey_status" id="survey_status2" value="closed" <?php echo set_radio('survey_status', 'closed', $this->form_data->survey_status == 'closed'); ?> /> Closed
                </label>
            </div>
        </div>

        <input value="yes"  type="hidden" name="survey_anms" id="survey_anms1" />


        <div class="control-group formSep">
            <label class="control-label" for="survey_expiry_date">Expiry Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="survey_expiry_date" id="exam_expiry_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('survey_expiry_date', $this->form_data->survey_expiry_date); ?>"
                        class="date input-small" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>

        
        <div class="form-actions">
            <input type="hidden" name="survey_id" value="<?php echo set_value('survey_id', $this->form_data->survey_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Survey" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<script type="text/javascript">

function decodeEntities(encodedString) {
  var div = document.createElement('span');
  div.innerHTML = encodedString;
  return div.textContent;
}


$( document ).ready(function() {
        $('input[name=exam_type]').on('change', function(e) {
            if($(this).val()==='mcq')
                $('.nevetive-mark-div').show();
            else{
                $('.nevetive-mark-div').hide();
            }
        });


        $('#added_ques_tbl_body').sortable();


        $(".loadQuestion").click(function(){
                var category_id=$.trim($("select[name=category_id]").val());
                var sub_cat_parent=$.trim($("select[name=sub_cat_parent]").val());
                var sub_two_cat_parent=$.trim($("select[name=sub_two_cat_parent]").val());
                var sub_three_cat_parent=$.trim($("select[name=sub_three_cat_parent]").val());
                var sub_four_cat_parent=$.trim($("select[name=sub_four_cat_parent]").val());

                var random=0;

                var question_type=0;
                if(category_id){ if(category_id.length==0){  category_id=0; } }
                if(sub_cat_parent){ if(sub_cat_parent.length==0){  sub_cat_parent=0; } }
                if(sub_two_cat_parent){ if(sub_two_cat_parent.length==0){  sub_two_cat_parent=0; } }
                if(sub_three_cat_parent){ if(sub_three_cat_parent.length==0){  sub_three_cat_parent=0; } }
                if(sub_four_cat_parent){ if(sub_four_cat_parent.length==0){  sub_four_cat_parent=0; } }

                //------------------------Ajax Customer Start-------------------------//
                 var AddHowMowKhaoUrl="<?=site_url('administrator/exam/surveyloadQuestionByCat')?>";
                 $.ajax({
                    'async': true,
                    'type': "POST",
                    'global': true,
                    'cache' : false,
                    'dataType': 'json',
                    'data': {
                                'category_id':category_id,
                                'sub_cat_parent':sub_cat_parent,
                                'sub_two_cat_parent':sub_two_cat_parent,
                                'sub_three_cat_parent':sub_three_cat_parent,
                                'sub_four_cat_parent':sub_four_cat_parent
                            },
                    'url': AddHowMowKhaoUrl,
                    'success': function (data) {
                        console.log("Counter Display Status : "+data); 


                        if(data.length>0)
                        {
                            $.each(data,function(index,row){
                                var mandatory_check='';
                                if(row.is_mandatory!==0 || row.is_mandatory!==null ){mandatory_check='check=""'};
                                var rowString="<tr id='data_ini_"+row.id+"'><td>"+(index+1)+"</td><td>"+decodeEntities(row.ques_text)+"</td><td><button type='button' name='question_list[]' data-ques='"+row.ques_text+"' data-mand='"+row.is_mandatory+"' data-cat='"+row.cat+"' data-mark='"+row.mark+"' data-ques='"+row.ques_text+"'   data-id='"+row.id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>";
                                console.log(rowString);

                                $("#ques_tbl_body").append(rowString);


                            });

                            var countData=data.length;
                            if(countData>3)
                            {
                                countData=4;
                            }
                            else
                            {
                                countData=data.length;
                            }

                            $.each(data,function(index,row){
                
                                if(index<5)
                                {
                                    $('#data_ini_'+row.id).children('td:eq(2)').children('button').trigger('click');
                                    console.log(row.id);
                                }
                            });

                            fixSerialForAddedQ();
                        }
                        else
                        {
                            alert("0 Question Found");
                        }

                        
                    }
                });
                //------------------------Ajax Customer End---------------------------//

            });

            <?php 
            if(isset($is_edit))
            {
                if(isset($modify_question))
                {
                    ?>
                var quesData=<?=json_encode($modify_question)?>;
                $.each(quesData,function(index,row){
                    var mandatory_check='';
                    if(row.is_mandatory!==0 || row.is_mandatory!==null ){mandatory_check='check=""'};
                    var rowString="<tr id='data_ini_"+row.id+"'><td>"+(index+1)+"</td><td>"+decodeEntities(row.ques_text)+"</td><td><button type='button' name='question_list[]' data-ques='"+row.ques_text+"' data-mand='"+row.is_mandatory+"' data-cat='"+row.cat+"' data-mark='"+row.mark+"' data-ques='"+row.ques_text+"'   data-id='"+row.id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>";
                    //console.log(rowString);

                    $("#ques_tbl_body").append(rowString);
                });

                var countData=quesData.length;
                

                $.each(quesData,function(index,row){
                
                    if(index<5)
                    {
                        $('#data_ini_'+row.id).children('td:eq(2)').children('button').trigger('click');
                        console.log(row.id);
                    }
                });

                fixSerialForAddedQ();

                <?php 
                }
                
            }
            ?>


    });

    function SelectAllSelectOptions() {
        $('#target option').prop('selected',true);
    }

    function getQuestions(elem) {
       
            $("#source").empty().append("");
            $('#ques_tbl_body').empty().append("");

            var type = $('input[name=exam_type]:checked').val();
            var cat = $(elem+':selected').text();
            $.ajax({
                method: "GET",
                dataType: 'json',
                url: link + "getcategorysurveyquestion/"+$(elem).val(),
                data:{type:type}
            }).done(function (obj) {
                var dataobjQues=obj.question;
                var dataobjQuesRand=obj.questionrand;
                console.log(dataobjQues);
                for (var i = 0; i < dataobjQues.length; i++)
                {
                    var mandatory_check='';
                    if(dataobjQues[i].is_mandatory!==0 || dataobjQues[i].is_mandatory!==null ){mandatory_check='check=""'};

                    $("#ques_tbl_body").append("<tr id='data_ini_"+dataobjQues[i].id+"'><td>"+(i+1)+"</td><td>"+dataobjQues[i].ques_text+"</td><td><button type='button' name='question_list[]' data-ques='"+dataobjQues[i].ques_text+"' data-mand='"+dataobjQues[i].is_mandatory+"' data-cat='"+cat+"' data-mark='"+dataobjQues[i].mark+"' data-ques='"+dataobjQues[i].ques_text+"'   data-id='"+dataobjQues[i].id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>");

                }

                $.each(dataobjQues,function(index,row){
                
                    if(index<5)
                    {
                        $('#data_ini_'+row.id).children('td:eq(2)').children('button').trigger('click');
                        console.log(row.id);
                    }
                });

                fixSerialForAddedQ();

                /*for (var i = 0; i < dataobjQuesRand.length; i++)
                {
                    $('#data_ini_'+dataobjQuesRand[i].id).children('td:eq(1)').children('button').click();
                }*/


            });
       

    }


    function form_priority_validation(){
        var valid = true;
        var limit = $('#set_limit').val();
        var items = document.getElementsByClassName('tr-number');
        var ques_num = items.length;



        return valid;
    }

    var sl=1;

    function addQuestionAsSelected(elem) {

        var totalmark=0;
        var is_mandatory="";
        if($(elem).data('mand')!==0)
            is_mandatory='checked=""';
        else
            is_mandatory='';

        var content = $(elem).data('ques');
        if ($('#added_ques_tbl_body:contains('+content+')').length == 0) {
            $("#added_ques_tbl_body").append("<tr class='tr-number'><td>"+sl+"</td><td id='ques_text'>"+$(elem).data('ques')+"</td><td><input type='hidden' name='question_ids[]' value='" + $(elem).data('id') + "' /><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");

            sl++;
        }
        else{
            alert('Adding same question is not allowed.');
        }

        fixSerialForAddedQ();
        
    }



    function AddAllSelectedQuestion() {
        var items = document.getElementsByClassName('question_list');
        var j = 0;
        for (var i = 0; i < items.length; i++) {
            var content = items[i].dataset.ques;
            if ($('#added_ques_tbl_body:contains('+content+')').length == 0) {
                var is_mandatory = "";
                if (items[i].dataset.mand !== '0')
                    is_mandatory='checked=""';
                else
                    is_mandatory = '';
                if(items[i].dataset.mark===null || items[i].dataset.mark==='null')
                    items[i].dataset.mark=0;
                else
                    items[i].dataset.mark=parseFloat(items[i].dataset.mark).toFixed(2);
                $("#added_ques_tbl_body").append("<tr class='tr-number'><td>"+(i+1)+"</td><td id='ques_text'>" + items[i].dataset.ques + "</td><td><input type=\"checkbox\" value=\"1\" name=\"is_mandatory_" + items[i].dataset.id + "\" id=\"is_mandatory\"  class='form-control input-sm'" +is_mandatory+"></td><td><input type='hidden' name='id[]' value='" + items[i].dataset.id + "' /><input name='mark_" + items[i].dataset.id + "' onkeyup='totalmarkcount()' class='mark-class' style='text-align:center;' value='" + items[i].dataset.mark + "' /></td><td><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");
            }
            else {
                if(j<=0) {
                    alert('Adding same question is not allowed.');
                }
                j++;
            }
        }

        fixSerialForAddedQ();
       
    }

    function fixSerialForAddedQ()
    {
        var totalAllTR=$("#added_ques_tbl_body tr");
        var totalTR=totalAllTR.length;
        console.log('Total TR =',totalTR);

        if(totalTR>0)
        {
            $.each(totalAllTR,function(index,row){
                console.log(row);
                $(row).children('td:eq(0)').html((index+1));
                //.children('td:eq(1)')
            });
        }

        var totalTBRAllTR=$("#ques_tbl_body tr");
        var totalTBRTR=totalTBRAllTR.length;
        console.log('Total TR =',totalTBRTR);

        if(totalTBRTR>0)
        {
            $.each(totalTBRAllTR,function(index,row){
                console.log(row);
                $(row).children('td:eq(0)').html((index+1));
                //.children('td:eq(1)')
            });
        }


        //ques_tbl_body

    }




    $(document).ready(function(){
        //$("button[name=mappingButton]").text();

        $("#mappingButton").css("pointer-events","none");

        $("input[name=set_limit]").keyup(function(){
            
        });

        $("input[name=set_mark]").keyup(function(){
            
        });
    });


    function removeQuestionAsSelected(elem) {
        $(elem).closest("tr").remove();
        fixSerialForAddedQ();
        
    }

    function sureTransfer(from, to, all) {
        if ( from.getElementsByTagName && to.appendChild ) {
            while ( getCount(from, !all) > 0 ) {
                transfer(from, to, all);
            }
        }
    }

    function getCount(target, isSelected) {
        var options = target.getElementsByTagName("option");
        if ( !isSelected ) {
            return options.length;
        }
        var count = 0;
        for ( i = 0; i < options.length; i++ ) {
            if ( isSelected && options[i].selected ) {
                count++;
            }
        }
        return count;
    }

    function transfer(from, to, all) {
        if ( from.getElementsByTagName && to.appendChild ) {
            var options = from.getElementsByTagName("option");
            for ( i = 0; i < options.length; i++ ) {
                if ( all ) {
                    to.appendChild(options[i]);
                } else {
                    if ( options[i].selected ) {
                        to.appendChild(options[i]);
                    }
                }
            }
        }
    }
    window.onload = function(){

    }

 

</script>




<script type="text/javascript">

    function loadCategory()
    {
        var def_category_id=0;
        <?php 
        if(isset($is_edit))
        {
            if(isset($this->form_data->category_id))
            {
                if($this->form_data->category_id>0)
                {
                    ?>
                    def_category_id=<?=$this->form_data->category_id?>;
                    <?php 
                }
            }
        }
        ?>
        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/survey_category/load_category')?>";
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
                        if(def_category_id>0 && row.id==def_category_id)
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

                if(def_category_id>0)
                {
                    $('select[name=category_id]').trigger("change");
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubCategory(cid)
    {
        var def_sub_category_id=0;
        <?php 
        if(isset($is_edit))
        {
            if(isset($this->form_data->sub_category_id))
            {
                if($this->form_data->sub_category_id>0)
                {
                    ?>
                    def_sub_category_id=<?=$this->form_data->sub_category_id?>;
                    <?php 
                }
            }
        }
        ?>
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/survey_category/load_sub_category')?>";
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
                            if(def_sub_category_id>0 && row.id==def_sub_category_id)
                            {
                                htmlString +='<option  selected="selected"  value="'+row.id+'">'+row.cat_name+'</option>';
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

                if(def_sub_category_id>0)
                {
                    $('select[name=sub_cat_parent]').trigger("change");
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubTwoCategory(cid,SubCat)
    {
        var def_sub_two_category_id=0;
        <?php 
        if(isset($is_edit))
        {
            if(isset($this->form_data->sub_two_category_id))
            {
                if($this->form_data->sub_two_category_id>0)
                {
                    ?>
                    def_sub_two_category_id=<?=$this->form_data->sub_two_category_id?>;
                    <?php 
                }
            }
        }
        ?>
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/survey_category/load_sub_two_category')?>";
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
                                if(def_sub_two_category_id>0 && row.id==def_sub_two_category_id)
                                {
                                    htmlString +='<option  selected="selected"  value="'+row.id+'">'+row.cat_name+'</option>';
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

                if(def_sub_two_category_id>0)
                {
                    $('select[name=sub_two_cat_parent]').trigger("change");
                }

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubThreeCategory(cid,SubCat,SubTwoCat)
    {
        var def_sub_three_category_id=0;
        <?php 
        if(isset($is_edit))
        {
            if(isset($this->form_data->sub_three_category_id))
            {
                if($this->form_data->sub_three_category_id>0)
                {
                    ?>
                    def_sub_three_category_id=<?=$this->form_data->sub_three_category_id?>;
                    <?php 
                }
            }
        }
        ?>
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/survey_category/load_sub_three_category')?>";
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
                                    if(def_sub_three_category_id>0 && row.id==def_sub_three_category_id)
                                    {
                                        htmlString +='<option  selected="selected"  value="'+row.id+'">'+row.cat_name+'</option>';
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
                if(def_sub_three_category_id>0)
                {
                    $('select[name=sub_three_cat_parent]').trigger("change");
                }
            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubFourCategory(cid,SubCat,SubTwoCat,SubThreeCat)
    {
        var def_sub_four_category_id=0;
        <?php 
        if(isset($is_edit))
        {
            if(isset($this->form_data->sub_four_category_id))
            {
                if($this->form_data->sub_four_category_id>0)
                {
                    ?>
                    def_sub_four_category_id=<?=$this->form_data->sub_four_category_id?>;
                    <?php 
                }
            }
        }
        ?>
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_four_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/survey_category/load_sub_four_category')?>";
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
                                        if(def_sub_four_category_id>0 && row.id==def_sub_four_category_id)
                                        {
                                            htmlString +='<option  selected="selected"  value="'+row.id+'">'+row.cat_name+'</option>';
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



