<main id="obj-editor">

    <div class="heading">
        <h2><?= $obj->ID == 0 ? "New Obj" : "Obj: OBJECT NAME"; ?></h2>
        <a href="<?=base_url('Obj/Listing');?>" class="primary-link pull-right">Back to Objs</a>
    </div>
        
    <form action="javascript:void(0);" class="row" data-obj-form>
        <div class="col-xs-12">

            <fieldset class="well">

                <h3>Fieldset Heading</h3>

                <!-- Text Input -->
                <div class="row">
                    <div class="col-sm-4 col-md-2">
                        <label for="field-1-field">Field 1</label>
                    </div>
                    <div class="col-sm-8 col-md-5">
                        <div class="form-group">
                            <input type="text" name="field-1" id="field-1-field" value="" />
                        </div>
                    </div>
                </div>

                <!-- Radio Buttons -->
                <div class="row">
                    <div class="col-sm-4 col-md-2">
                        <div class="label">Field 2</div>
                    </div>
                    <div class="col-sm-8 col-md-5">
                        <div class="form-group radio-horizontal">
                            <input type="radio" id="field2-1-field" name="field2" data-custom-element="radio" />
                            <label for="field2-1-field">Field 2 Option 1</label>
                            <input type="radio" id="field2-2-field" name="field2" data-custom-element="radio" />
                            <label for="field2-2-field">Field 2 Option 2</label>
                        </div>
                    </div>
                </div>

                <!-- Checkboxes -->
                <div class="row">
                    <div class="col-sm-4 col-md-2">
                        <div class="label">Field 3</div>
                    </div>
                    <div class="col-sm-8 col-md-5">
                        <div class="form-group">
                            <input type="checkbox" id="field3-1-field" name="field3-1" data-custom-element="checkbox" />
                            <label for="field3-1-field">Field 3 Option 1</label>
                            <input type="checkbox" id="field3-2-field" name="field3-2" data-custom-element="checkbox" />
                            <label for="field3-2-field">Field 3 Option 2</label>
                        </div>
                    </div>
                </div>

                <!-- Dropdown -->
                <div class="row">
                    <div class="col-sm-4 col-md-2">
                        <label for="field-4-field">Field 4</label>
                    </div>
                    <div class="col-sm-8 col-md-5">
                        <div class="form-group">
                            <select id="field-4-field" name="field-4" data-custom-element="dropdown">
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Textarea -->
                <div class="row">
                    <div class="col-sm-4 col-md-2">
                        <label for="field-5-field">Field 5</label>
                    </div>
                    <div class="col-sm-8 col-md-5">
                        <div class="form-group">
                            <textarea name="" id="field-5-field" rows="4"></textarea>
                        </div>
                    </div>
                </div>

            </fieldset>

            <div class="button-container">
                <button type="submit" class="btn btn-primary uppercase" data-submit-btn>Submit</button>
            </div>
                
        </div>
    </form>
            
</main>

<script>
    $(document).ready(function(){

        var objForm = $('[data-obj-form]').autoForm({
            SubmitURL: '<?=base_url('Obj/Save/' . $obj->ID);?>',
            FormData: {
                id: <?=$obj->ID;?>
            }
        });
        
        <?php if($obj->ID == 0){ ?>
        objForm.AddCallback(function(object){
            window.location = '<?=base_url('Obj/Editor');?>/' + object.ID;
        });
        <?php } ?>
    });
</script>