<?php require_once("header.php");?>
<div id="content">
    <?php if(!isset($_POST["step"])){?>
    <form method="POST">
        <input type="hidden" name="step" value="1">
        <h2><b>Schritt 1:</b> Gib hier deine MQTT Ausgabe ein</h2>
        <textarea id="mqttoutput" name="mqttoutput" rows="4" cols="50"></textarea><br>
        <input type="submit" value="Weiter">
    </form>
    <?php } ?>
    <?php if($_POST["step"] == 1){
        # MQTT Ausgabe verarbeiten
        $mqttoutput = $_POST["mqttoutput"];
        $mqttoutput = explode("\n", $mqttoutput);
        $sensorvalues = array();
        foreach($mqttoutput as $line){
            $line = explode(" ", $line);
            # Letzter Wert ermitteln
            $sensorvalues[$line[0]] = array("value" => $line[1]);
            # Homeassistant ID ermitteln
            $explodedtopic = explode("/", $line[0]);
            $sensorvalues[$line[0]]["id"] = $explodedtopic[1] . "_" . str_replace(array(" ", ".", "-"), "_", $explodedtopic[2]);
            # Anzeigename ermitteln
            $sensorvalues[$line[0]]["name"] = ucwords(str_replace("_", " ", $sensorvalues[$line[0]]["id"]));
            # Geräteklasse und Einheit ermitteln
            if(str_contains($explodedtopic[2], "faktor")){
                $sensorvalues[$line[0]]["device_class"] = "None";
                $sensorvalues[$line[0]]["unit"] = "";
            } elseif(str_contains($explodedtopic[2], "spannung")){
                $sensorvalues[$line[0]]["device_class"] = "voltage";
                $sensorvalues[$line[0]]["unit"] = "V";
            } elseif(str_contains($explodedtopic[2], "strom")){
                $sensorvalues[$line[0]]["device_class"] = "current";
                $sensorvalues[$line[0]]["unit"] = "A";
            } elseif(str_contains($explodedtopic[2], "temp")){
                $sensorvalues[$line[0]]["device_class"] = "temperature";
                $sensorvalues[$line[0]]["unit"] = "°C";
            } elseif(str_contains($explodedtopic[2], "leistung")){
                $sensorvalues[$line[0]]["device_class"] = "power";
                $sensorvalues[$line[0]]["unit"] = "W";
            } elseif(str_contains($explodedtopic[2], "wh")){
                $sensorvalues[$line[0]]["device_class"] = "energy";
                $sensorvalues[$line[0]]["unit"] = "Wh";
            } elseif(str_contains($explodedtopic[2], "frequenz")){
                $sensorvalues[$line[0]]["device_class"] = "frequency";
                $sensorvalues[$line[0]]["unit"] = "Hz";
            } elseif(str_contains($explodedtopic[2], "soe")){
                $sensorvalues[$line[0]]["device_class"] = "battery";
                $sensorvalues[$line[0]]["unit"] = "%";
            } 
            else {
                $sensorvalues[$line[0]]["device_class"] = "None";
                $sensorvalues[$line[0]]["unit"] = "";
            }
        }
        # Ausgabe
        ?>
            <form method="POST">
                <input type="hidden" name="step" value="2">
                <h2><b>Schritt 2:</b> Hier sind deine Daten. Du kannst sie jetzt noch beliebig verändern</h2>
                Gesamt: <?= count($sensorvalues)?> Sensoren gefunden<br>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">In HA einbinden</th>
                            <th scope="col">MQTT Topic</th>
                            <th scope="col">Letzter Wert</th>
                            <th scope="col">Homeassistant ID</th>
                            <th scope="col">Homeassistant Anzeigename</th>
                            <th scope="col">Homeassistant Geräteklasse</th>
                            <th scope="col">Homeassistant Einheit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sensorvalues as $topic => $values){ ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" name="<?= $topic?>_use" checked>
                                    </div>
                                </td>
                                <td><?php echo $topic; ?></td>
                                <td><?php echo $values["value"]; ?></td>
                                <td>
                                    <input class="form-control" type="text" name="<?= $topic?>_ha_id" value="<?= $values["id"]?>">
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="<?= $topic?>_ha_name" value="<?= $values["name"]?>">
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="<?= $topic?>_ha_class" id="<?= $topic?>_ha_id_none" value="none"<?php if($values["device_class"] == "None") echo " checked";?>>
                                        <label class="form-check-label" for="<?= $topic?>_ha_id_none">
                                            Keine
                                        </label>
                                    </div>
                                    <?php
                                        $options = array("voltage" => "Spannung", "current" => "Strom", "temperature" => "Temperatur", "power" => "Leistung", "energy" => "Energie", "frequency" => "Frequenz", "battery" => "Batteriefüllstand");
                                        foreach($options as $option => $optionname){
                                            ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="<?= $topic?>_ha_class" id="<?= $topic?>_ha_id_<?= $option?>" value="<?= $option?>"<?php if($values["device_class"] == $option) echo " checked";?>>
                                                <label class="form-check-label" for="<?= $topic?>_ha_id_none">
                                                    <?= $optionname ?>
                                                </label>
                                            </div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <input class="form-control" type="text" name="<?= $topic?>_ha_unit" value="<?= $values["unit"]?>">
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <input type="submit" value="Weiter">
        <?php
    } if($_POST["step"] == 2){
    $haconfig = "
mqtt:
  sensor:";
        $count_sensors = 0;
        foreach($_POST as $key => $value){
            if(str_contains($key, "_use")){
                $count_sensors++;
                $explodedkey = explode("_", $key);
                array_pop($explodedkey);
                $topic = implode("_", $explodedkey);
                $id = $_POST[$topic."_ha_id"];
                $name = $_POST[$topic."_ha_name"];
                $device_class = $_POST[$topic."_ha_class"];
                $unit = $_POST[$topic."_ha_unit"];
$haconfig .= '
    - name: "'.$name.'"
      object_id: "'.$id.'"
      state_topic: "'.$topic.'"
      unit_of_measurement: "'.$unit.'"';
                if($device_class != "none"){
$haconfig .= '
      device_class: "'.$device_class.'"
';
                } else {
$haconfig .= '
';
                }
            }
        }
        echo "<h2><b>Schritt 3:</b> Hier ist deine Homeassistant Konfiguration. Kopiere sie in deine config.yaml Datei</h2>";
        echo "# Gesamt: ".$count_sensors." Sensoren ausgewählt<br>";
        echo "<pre>".$haconfig."</pre>";
    }
    ?>
    <?php require_once("footer.php");?>
</div>