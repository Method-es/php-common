<tr data-obj-id="<?= $Obj->ID; ?>">
    <td><?= $Obj->Field1; ?></td>
    <td><?= $Obj->Field2; ?></td>
    <td><?= $Obj->Field3; ?></td>
    <td><?= $Obj->Field4; ?></td>
    <td class="list-options">
        <!-- use anchor tags when the action is likely NOT handled by ajax (so page navigation is desired) -->
        <a data-list-option="edit" href="<?=base_url("Objs/Editor/{$Obj->ID}");?>" class="reverse-colors"><span class="fa fa-pencil"></span></a>
        <a data-list-option="delete" href="<?=base_url("Objs/Delete/{$Obj->ID}");?>" class="reverse-colors"><span class="fa fa-trash"></span></a>
        <!-- use button elements when you KNOW the request will be handled via ajax/javascript -->
        <button type="button" data-list-option="remove"><span class="text-danger fa fa-times"></span></button>
        <button type="button" data-list-option="edit"><span class="fa fa-edit"></span></button>
    </td>
</tr>