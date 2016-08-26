    <div class="stacked-list card-list">
        <?php
        foreach($Objs as $Obj){
            echo $this->load->view('Obj/stacked-list-card',['Obj'=>$Obj],true);
        }
        ?>
    </div>