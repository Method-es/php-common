    <div class="tiled-list card-list">
        <?php
        foreach($Objs as $Obj){
            echo $this->load->view('Obj/tiled-list-card',['Obj'=>$Obj],true);
        }
        ?>
    </div>