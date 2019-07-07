<h1 class="heading"><?php if(@$questionSet) echo 'Edit'; else echo 'Add New'; ?> Set</h1>
<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>
<?php if(@$questionSet) { ?>
    <?php echo form_open('administrator/question_set/update_question_set', array('class' => 'form-horizontal','onsubmit' => 'return form_priority_validation();')); ?>
<?php }else{?>
    <?php echo form_open('administrator/exam/mappingquestion', array('class' => 'form-horizontal','onsubmit' => 'return form_priority_validation();')); ?>
<?php } ?>
<fieldset>
    <?php if(@$questionSet) { ?>
        <input type="hidden" name="setid" value="<?php echo $questionSet[0]['id']; ?>">
    <?php } ?>
    <div class="control-group formSep">
        <label class="control-label" for="set_name">Set Name</label>
        <div class="controls">
            <input required type="text" name="set_name" id="set_name" value="<?php echo @$questionSet[0]['name']; ?>" class="input-xxlarge" />
            <!--<span class="help-inline">Inline help text</span>-->
        </div>
    </div>

    <div class="control-group formSep">
        <label class="control-label" for="cat_parent">Total Question</label>
        <div class="controls">
            <input required type="number"  name="set_limit" id="set_limit" value="<?php echo @$questionSet[0]['set_limit']; ?>" class="input-xxlarge" />

        </div>
    </div>

    <div class="control-group formSep">
        <label class="control-label" for="set_mark">Total Mark</label>
        <div class="controls">
            <input required type="number"  name="set_mark" id="set_mark" value="<?php echo @$questionSet[0]['total_mark']; ?>" class="input-xxlarge" />

        </div>
    </div>

<!-- onchange="checkQuestionLimitPoolQuestion()" onkeyup="checkQuestionLimitPoolQuestion()" -->

    <div class="control-group formSep">
        <label class="control-label" for="exam_type1">Type</label>
        <div class="controls">
            <label class="radio inline">
                <input type="radio" name="exam_type" onchange="manageNegativeDiv(this.value)"  id="exam_type1" value="mcq" checked /> Multiple Choice Question (MCQ)
            </label>
            <label class="radio inline">
                <input type="radio" name="exam_type" id="exam_type2" value="descriptive"  /> Descriptive Question
            </label>
        </div>
    </div>

    <div class="control-group formSep">
        <label class="control-label" for="exam_type1">Question Random</label>
        <div class="controls">
            <label class="radio inline">
                <input type="radio" name="random" id="random_0" value="random" <?php if(isset($questionSet)) { if($questionSet[0]['random_qus']=="random" || $questionSet[0]['random_qus']=="") { echo "checked"; }} ?> /> Yes
            </label>
            <label class="radio inline">
                <input type="radio" name="random" id="random_1" value="fixed" <?php if(isset($questionSet)) { if($questionSet[0]['random_qus']=="fixed") { echo "checked"; }} ?>/> No
            </label>
        </div>
    </div>

    <!--

    <div class="control-group formSep nevetive-mark-div">
        <label class="control-label" for="exam_type1">Negative Mark Per Question</label>
        <div class="controls">
            <input  type="number" name="neg_mark" step="0.01" id="neg_mark" value="<?php //if(isset($questionSet)) {//echo @$questionSet[0]['neg_mark_per_ques']; } ?>" class="input-xxlarge" />
        </div>
    </div>
    -->

    <?php /*print_r_pre($exaCategory); ?>

    <!-- <div class="control-group">
        <label class="control-label" for="cat_parent">Questions Category</label>
        <div class="controls">
            <select onchange="getQuestions(this)" name="ques_cat" id="ques_cat" class="chosen-select input-xxlarge">
                <option value="">Select Category</option>
                <?php //foreach ($exaCategory as $key =>$value) { 
                    ?>
                    <option value="<?php //echo $value->id; ?>"><?php //echo $value->cat_name; ?></option>
                <?php } ?>
            </select>
        </div>
    </div> --> */ ?>

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
                <th width="200">Category</th>
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
                <th width="200">Category</th>
                <th width="500">Questions</th>
                <th width="120">Is Mandatory?</th>
                <th width="50">Mark</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="added_ques_tbl_body">
            <?php if(@$setData) { ?>


            <?php 

            //print_r_pre($setData);

            $ik=1;
            foreach ($setData as $key) { ?>

                <tr class="tr-number">
                    <td><?php echo $ik; ?></td>
                    <td id="ques_text"><?php echo html_entity_decode($key['cat']); ?></td>
                    <td id="ques_text"><?php echo html_entity_decode($key['ques_text']); ?></td>
                    <td style="text-align: center;"><input type="checkbox" value="1" name="is_mandatory_<?php echo $key['id']; ?>" id="is_mandatory" class="form-control input-sm" <?php if($key['mandatory_check']=='1') echo 'checked=""'; ?> /></td>
                    <td><input type="hidden" name="id[]" value="<?php echo $key['id']; ?>"><input  name="mark_<?php echo $key['id']; ?>" class="mark-class" style="text-align:center; width: 100px;" value="<?php echo $key['current_mark']; ?>"></td>
                    <td><button type="button" name="question_list[]" id="remove_ques" onclick="removeQuestionAsSelected(this)" class="form-control btn btn-success input-sm">Remove</button></td>


                </tr>
            <?php 
            $ik++;
            } ?>


            <?php } ?>




            </tbody>
        </table>

        <div class="control-group formSep">

        </div>


        <div class="control-group formSep">
            Total Mark : <span id="total_mark"></span>
            <input type="hidden" name="total_mark" id="total_mark_id" value="">
        </div>


        <div class="form-actions" style="margin-top:5%;">
            <button  type="submit" id="mappingButton" name="mappingButton" class="btn btn-primary btn-large"><?php if(isset($setData)) { echo "Update"; }else{ echo "Set"; } ?> Mapping </button>
            <span id="poolErrorMSG"></span>
        </div>

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
            if(document.getElementById('random_0').checked==true)
            {
                random=1;
            }

            var question_type=0;
            if(document.getElementById('exam_type1').checked==true)
            {
                question_type=1;
            }
            else if(document.getElementById('exam_type2').checked==true)
            {
                question_type=2;
            }

            if(category_id){ if(category_id.length==0){  category_id=0; } }
            if(sub_cat_parent){ if(sub_cat_parent.length==0){  sub_cat_parent=0; } }
            if(sub_two_cat_parent){ if(sub_two_cat_parent.length==0){  sub_two_cat_parent=0; } }
            if(sub_three_cat_parent){ if(sub_three_cat_parent.length==0){  sub_three_cat_parent=0; } }
            if(sub_four_cat_parent){ if(sub_four_cat_parent.length==0){  sub_four_cat_parent=0; } }

            //------------------------Ajax Customer Start-------------------------//
             var AddHowMowKhaoUrl="<?=site_url('administrator/exam/loadQuestionByCat')?>";
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
                            'sub_four_cat_parent':sub_four_cat_parent,
                            'random':random,
                            'question_type':question_type,
                        },
                'url': AddHowMowKhaoUrl,
                'success': function (data) {
                    console.log("Counter Display Status : "+data); 


                    if(data.length>0)
                    {

                        $.each(data,function(index,row){
                            var mandatory_check='';
                            if(row.is_mandatory!==0 || row.is_mandatory!==null ){mandatory_check='check=""'};
                            var rowString="<tr id='data_ini_"+row.id+"'><td>"+(index+1)+"</td><td>"+decodeEntities(row.cat)+"</td><td>"+decodeEntities(row.ques_text)+"</td><td><button type='button' name='question_list[]' data-ques='"+row.ques_text+"' data-mand='"+row.is_mandatory+"' data-cat='"+row.cat+"' data-mark='"+row.mark+"' data-ques='"+decodeEntities(row.ques_text)+"'   data-id='"+row.id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>";
                            //console.log(rowString);

                            $("#ques_tbl_body").append(rowString);
                            
                            

                        });

                        var getActLen=data.length;
                        var autoAddlen=5;
                        if(getActLen>5)
                        {
                            autoAddlen=4;
                        }
                        else
                        {
                            autoAddlen=getActLen;
                        }

                        $.each(data,function(index,row){
                            if(index<5)
                            {
                                $('#data_ini_'+row.id).children('td:eq(3)').children('button').trigger('click');
                                console.log(row.id);
                            }
                                
                        });
                    }
                    else
                    {
                        alert("0 Question Found");
                    }

                    
                }
            });
            //------------------------Ajax Customer End---------------------------//

        });
    });

    function SelectAllSelectOptions() {
        $('#target option').prop('selected',true);
    }

    function getQuestions(elem) {
        var set_mark = $("#set_mark").val();
        if(set_mark)
        {
            $("#source").empty().append("");
            $('#ques_tbl_body').empty().append("");

            var type = $('input[name=exam_type]:checked').val();
            var cat = $(elem+':selected').text();
            $.ajax({
                method: "GET",
                dataType: 'json',
                url: link + "getcategoryquestion/"+$(elem).val(),
                data:{type:type}
            }).done(function (obj) {
                var dataobjQues=obj.question;
                var dataobjQuesRand=obj.questionrand;
                console.log(dataobjQues);
                for (var i = 0; i < dataobjQues.length; i++)
                {
                    var mandatory_check='';
                    if(dataobjQues[i].is_mandatory!==0 || dataobjQues[i].is_mandatory!==null ){mandatory_check='check=""'};

                    $("#ques_tbl_body").append("<tr id='data_ini_"+dataobjQues[i].id+"'><td>"+(i+1)+". "+dataobjQues[i].ques_text+"</td><td><button type='button' name='question_list[]' data-ques='"+dataobjQues[i].ques_text+"' data-mand='"+dataobjQues[i].is_mandatory+"' data-cat='"+cat+"' data-mark='"+dataobjQues[i].mark+"' data-ques='"+dataobjQues[i].ques_text+"'   data-id='"+dataobjQues[i].id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>");

                }



                for (var i = 0; i < dataobjQuesRand.length; i++)
                {
                    $('#data_ini_'+dataobjQuesRand[i].id).children('td:eq(3)').children('button').click();
                    //addQuestionAsSelected(elmn);
                    //console.log(i,elmn);
                }

                fixSerialForAddedQ();

            });
        }
        else {

            alert("Set Total Mark First.");
            return false;

        }

    }


    function form_priority_validation(){
        var valid = true;
        var limit = $('#set_limit').val();
        var items = document.getElementsByClassName('tr-number');
        var ques_num = items.length;



        if(ques_num>limit) {
            alert('Total quesion limit is ' + limit + '. Please remove question before submitting. ');
            valid=false;
        }

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
        //var catName = $(elem);
        //console.log('Cat Name',catName);
        if ($('#added_ques_tbl_body:contains('+content+')').length == 0) {
            $("#added_ques_tbl_body").append("<tr class='tr-number'><td>"+sl+"</td><td id='ques_text'>"+$(elem).attr('data-cat')+"</td><td id='ques_text'>"+$(elem).data('ques')+"</td><td><input style='text-align:center;' type='checkbox' value='1' name='is_mandatory_" + $(elem).data('id') +"' id='is_mandatory'  class='form-control input-sm'" +is_mandatory+"></td><td><input type='hidden' name='id[]' value='" + $(elem).data('id') + "' /><input  name='mark_" + $(elem).data('id') + "' class='mark-class' style='text-align:center; width: 100px;' value='"+$(elem).data('mark')+"' onkeyup='totalmarkcount()' /></td><td><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");

            sl++;
        }
        else{
            alert('Adding same question is not allowed.');
        }
        totalmarkcount();
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
                $("#added_ques_tbl_body").append("<tr class='tr-number'><td id='ques_text'>" + items[i].dataset.ques + "</td><td><input type=\"checkbox\" value=\"1\" name=\"is_mandatory_" + items[i].dataset.id + "\" id=\"is_mandatory\"  class='form-control input-sm'" +is_mandatory+"></td><td><input type='hidden' name='id[]' value='" + items[i].dataset.id + "' /><input name='mark_" + items[i].dataset.id + "' onkeyup='totalmarkcount()' class='mark-class' style='text-align:center;' value='" + items[i].dataset.mark + "' /></td><td><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");
            }
            else {
                if(j<=0) {
                    alert('Adding same question is not allowed.');
                }
                j++;
            }
        }
        totalmarkcount();
    }




    function totalmarkcount(){
        var items = document.getElementsByClassName('mark-class');
        var totalmarks=0.0;
        for (var i = 0; i < items.length; i++) {

            if(items[i].value===null || items[i].value==='null')
                items[i].value=0.0;
            console.log(parseFloat(totalmarks)+" "+ parseFloat(items[i].value));

            totalmarks=parseFloat(totalmarks)+parseFloat(items[i].value);
        }
        $('#total_mark').text(parseFloat(totalmarks).toFixed(2));
        $('#total_mark_id').val(parseFloat(totalmarks).toFixed(2));

        checkQuestionLimitPoolQuestion(parseFloat(totalmarks).toFixed(2));

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

    function checkQuestionLimitPoolQuestion(totalMark)
    {
        var set_mark = $("#set_mark").val();
        if(!set_mark)
            set_mark = 0;
        if(!totalMark)
            totalMark = 0;
        var set_limit=$("input[name=set_limit]").val();
        var poolQuestionClass=$('.mark-class');
        var poolQuestionLength=poolQuestionClass.length;


        if((set_limit==poolQuestionLength) && (totalMark===parseFloat(set_mark).toFixed(2)))
        {
            $("button[name=mappingButton]").css("pointer-events","auto");
            //console.log('false');
            $("#poolErrorMSG").html("");
        }
        else
        {
            $("button[name=mappingButton]").css("pointer-events","none");
            console.log('true');
            $("#poolErrorMSG").html("<code>Button is now disbale, Until Fillup Question Limit and Set Mark</code>");
        }
        //alert(poolQuestionLength);
    }

    $(document).ready(function(){
        //$("button[name=mappingButton]").text();

        <?php 
        if(isset($setData)) { 

            ?>
            totalmarkcount();
            <?php

        }
            ?>



        $("#mappingButton").css("pointer-events","none");

        $("input[name=set_limit]").keyup(function(){
            totalmarkcount();
        });

        $("input[name=set_mark]").keyup(function(){
            totalmarkcount();
        });

        $(".mark-class").keyup(function(){
            totalmarkcount();
        });
    });


    function removeQuestionAsSelected(elem) {
        $(elem).closest("tr").remove();
        totalmarkcount();
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
                        htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                    });
                }

                $("select[name=category_id]").html(htmlString).chosen();
                $('select[name=category_id]').trigger("liszt:updated");

                $("select[name=sub_cat_parent]").html("").chosen();
                $('select[name=sub_cat_parent]').trigger("liszt:updated");                

                $("select[name=sub_two_cat_parent]").html("").chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_three_cat_parent]").html("").chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_four_cat_parent]").html("").chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubCategory(cid)
    {
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
                            htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                        }
                        
                    });
                }

                $("select[name=sub_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_two_cat_parent]").html("").chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_three_cat_parent]").html("").chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_four_cat_parent]").html("").chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubTwoCategory(cid,SubCat)
    {
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
                                htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                            }
                        }
                        
                    });
                }

                console.log(htmlString);

                $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_three_cat_parent]").html("").chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_four_cat_parent]").html("").chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubThreeCategory(cid,SubCat,SubTwoCat)
    {
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
                                    htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

                $("select[name=sub_four_cat_parent]").html("").chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubFourCategory(cid,SubCat,SubTwoCat,SubThreeCat)
    {
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
                                         htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
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


