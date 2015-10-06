    <table class="table-list">
        <thead>
            <tr>
                <!-- 35/30/15/15/5 -->
                <th style="width:35%" data-paginate-order-by="Field1" data-paginate-order-this >Field1</th>
                <th style="width:30%" data-paginate-order-by="Field2">Field2</th>
                <th style="width:15%" data-paginate-order-by="Field3">Field3</th>
                <th style="width:15%" data-paginate-order-by="Field4">Field4</th>
                <th style="width:5%"></th>
            </tr>
        </thead>
        <tbody>
        <?= $this->load->view('Objs/rows-list.php',['objs'=>$objs],true); ?>
        </tbody>
    </table>
