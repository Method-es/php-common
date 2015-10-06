<?php
foreach($objs as $obj){
    echo $this->load->view('Objs/list-row.php',['obj'=>$obj],true);
}
?>

