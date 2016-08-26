<table class="table-items">
<?= $this->load->view('Obj/table-list-rows',['Objs'=>$Objs],true); ?>
</table><div class="stacked-items">
<?= $this->load->view('Obj/stacked-list',['Objs'=>$Objs],true); ?>
</div>
