<form onsubmit="return false">
    <div style="display:none">
        <?php foreach ($hidden_fields as $hidden){
            echo "<input type='hidden' value='".(isset($hidden['value'])?$hidden['value']:(isset($this->data[$hidden['field']])?$this->data[$hidden['field']]:''))."'/>";
        } ?>
    </div>
    <table cellspacing="0" class="table table-bordered" id="table1">
        <tbody>
            <?php
            $i = 0;
            foreach ($fields as $key => $value) {
                if ($i % $col == 0) {
                    echo "<tr>";
                }
                echo "<td ".($per!=''?"style='width:".((100*$per)/(1+$per)/$col)."%'":'').">{$value['title']}</td>";
                echo "<td ".($per!=''?"style='width:".(100/(1+$per)/$col)."%'":'').">" . $this->format_input($value) . "</td>";
                $i++;
                if ($i % $col == 0) {
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>
</form>


