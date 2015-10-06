<tr data-obj-id="<?= $obj->ID; ?>">
    <td><?= $obj->Field1; ?></td>
    <td><?= $obj->Field2; ?></td>
    <td><?= $obj->Field3; ?></td>
    <td><?= $obj->Field4; ?></td>
    <td class="list-options">
        <a data-list-option="edit" href="<?=base_url("admin/Obj/Editor/{$obj->ID}");?>" class="reverse-colors"><span class="fa fa-pencil"></span></a>
        <a data-list-option="delete" href="<?=base_url("admin/Obj/Delete/{$obj->ID}");?>" class="reverse-colors"><span class="fa fa-trash"></span></a>
    </td>
</tr>