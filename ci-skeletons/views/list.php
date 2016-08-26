<main id="obj-list">
    
    <h2>Manage Objs</h2>
    <p class="lead">Add, Edit &amp; Delete Objs</p>
    
    <div class="form-group">
        <a href="<?=base_url('admin/Obj/Editor/0');?>" class="btn btn-primary">Add New <span class="fa fa-plus"></span></a>
    </div>
    
    <div class="row">
        <div class="col-xs-5">
            <div class="form-group">
                <div class="search-input">
                    <button class="fa fa-search" data-perform-search></button>
                    <input type="search" placeholder="" />
                    <button class="fa fa-times-circle" data-clear-search></button>
                </div>
            </div>
        </div>
        <div class="col-xs-5">
            <button class="btn btn-default" data-perform-search>Search</button>
        </div>
    </div>
    
    <?= 

        //by default this always uses the table listing;
        //if you would like other listing views, either replace this with the call, 
        // or if you are doing a mixed mode implementation, ensure to add the additional views you are mixing modes with
        $this->load->view('Objs/table-list.php',['objs'=>$objs],true); 
    ?>

    <div data-obj-paginator></div>

<script type="text/javascript">
$(document).ready(function(){

    Strut.Pagination.Create({
        Container: $('[data-obj-paginator]'),
        Target: $('#obj-list .table-list tbody'),
        Header: $('#obj-list .table-list thead'),
        DataSource: 'admin/Obj/Listing/json/rows',
        SearchField: $('.search-input input[type="search"]'),
        LoadOnInit: false,
        ItemsPerPage: <?= Obj::DEFAULT_LIMIT; ?>,
        TotalPages: <?= ceil($totalObjs / Obj::DEFAULT_LIMIT); ?>,
        PostDrawCallback: function(self){
            $(self.Target).listOptions([
                {
                    name: 'edit',
                    fullRow: true
                },
                {
                    name: 'edit',
                    tooltip: {
                        theme: 'primary'
                    }
                },
                {
                    name: 'delete',
                    ajax: true,
                    confirm: true,
                    tooltip: {
                        theme: "danger"
                    },
                    callback: function(data){
                        self.Refresh();
                    }
                }
            ]);
        },
        HistoryPopStateCallback: function(state){
            //ensure to handle the case in which state is null (reset items all to default)
            //you should always assume if it's not explictly set within state, then return it to the default
        }
    }, 'objPaginator');
    
});
</script>

</main>