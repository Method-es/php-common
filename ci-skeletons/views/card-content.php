    <div class="list-card display-card content-area">
        <div class="card-content">
            <div class="card-info info">
                <!-- put card content in here -->
                <!-- typical styling: 
                <h4>Main Information</h4>
                <p><strong>Secondary Information</strong></p>
                <p>
                    Key: <em>Value</em>
                    <br />Key: <em>Value</em>
                </p> 
                for grouping content: 
                <div class="info-group">
                    ... 
                </div> 
                -->
            </div>
            <!-- list options below, add or remove from here to enable -->
            <div class="list-options">
                <!-- use anchor tags when the action is likely NOT handled by ajax (so page navigation is desired) -->
                <a data-list-option="edit" href="<?=base_url("Objs/Editor/{$Obj->ID}");?>" class="reverse-colors"><span class="fa fa-pencil"></span></a>
                <a data-list-option="delete" href="<?=base_url("Objs/Delete/{$Obj->ID}");?>" class="reverse-colors"><span class="fa fa-trash"></span></a>
                <!-- use button elements when you KNOW the request will be handled via ajax/javascript -->
                <button type="button" data-list-option="remove"><span class="text-danger fa fa-times"></span></button>
                <button type="button" data-list-option="edit"><span class="fa fa-edit"></span></button>
            </div>
        </div>
    </div>
