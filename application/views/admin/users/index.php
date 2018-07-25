<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<link href="<?= pluginpath(); ?>jquery-datatable/extensions/FixedColumns/css/dataTables.fixedColumns.min.css" rel="stylesheet" type="text/css" />
<link href="<?= pluginpath(); ?>datatables-responsive/css/datatables.responsive.css" rel="stylesheet" type="text/css" media="screen" />

            <div class="content-wrapper">
                <section class="content-header">
                    <?php echo $pagetitle; ?>
                    <?php echo $breadcrumb; ?>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                             <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><?php echo anchor('admin/users/create', '<i class="fa fa-plus"></i> '. lang('users_create_user'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
                                </div>
                                <div class="box-body">
                                    <table class="table table-striped" id="noteslisting">
                                    </table>
                                </div>
                            </div>
                         </div>
                    </div>
                </section>
            </div>

    <script>
    $(function () {
    
    OTableNotes = $('#noteslisting').DataTable({
            "bProcessing": true,
            "bAutoWidth": false,
            "oLanguage": {"sProcessing": '<div class="overlay_loader"><img src="<?= imagepath(); ?>preload.gif" alt="loading" height="80" width="80"/></div>'},
            "iDisplayLength": 10,
            "sDom": 'r<"top"<"list-pagination"p>><"clear">t<"bottom"<"list-pagination"p>><"clear">',
            "bServerSide": true,
            "bPaginate": true,
            "bFilter": false,
            "bLengthChange": false,
            "order": [[0, 'asc'], [1, 'asc']],
            "sAjaxSource": "<?php echo base_url() ?>admin/Users/ajaxData",
            "fnServerData": function (sSource, aoData, fnCallback) {
                $.ajax({
                    "dataType": 'json',
                    "type": "POST",
                    "url": sSource,
                    "data": aoData,
                    "success": fnCallback
                });
            },
            "aoColumns": [
                {"sName": "first_name", 'sTitle': 'Notes', orderable: true, 'sClass': 'valign-middle', 'sWidth': '80%'},
                {"sName": "last_name", 'sTitle': 'Date', 'sClass': 'valign-middle', 'sWidth': '15%', bSearchable: false, bSortable: false},
                {"sName": "operations", 'sTitle': 'Action', 'sClass': 'valign-middle', 'sWidth': '5%', bSearchable: false, bSortable: false},
            ]
        });

    
});
</script>

 