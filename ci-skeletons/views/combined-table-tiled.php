<table class="table-items">
<?= $this->load->view('Obj/table-list-rows',['Objs'=>$Objs],true); ?>
</table><div class="tiled-items">
<?= $this->load->view('Obj/tiled-list',['Objs'=>$Objs],true); ?>
</div>
