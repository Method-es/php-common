<?php
foreach($Objs as $Obj){
    echo $this->load->view('Obj/table-list-content',['Obj'=>$Obj],true);
}
?>

