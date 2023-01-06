<?php require_once("header.php");?>
<div id="content-small">
    <h2>Vorbereitungen der Solaranzeige</h2>
    Die Solaranzeige muss so eingestellt sein, dass sie die Daten per MQTT an einen Broker sendet. Wie das geht erfährst du <a href="https://solaranzeige.de/phpBB3/download/MQTT%20Informationen%20zur%20Solaranzeige.pdf">hier</a>.<br>
    <h2>Vorbereitungen am Homeassistant</h2>
    Im Homeassistant muss dieser Broker konfiguriert werden. Dies kann über die Weboberfläche unter erfolgen. Weitere Informationen <a href="https://www.home-assistant.io/docs/mqtt/broker/">hier</a>.<br>
    <h2>MQTT Daten per Terminal abrufen</h2>
    Mit folgendem Befehl werden alle Channels auf dem MQTT Broker abonniert:<br>
    <code>mosquitto_sub -v -h broker_ip -p 1883 -t '#'</code><br>
    Die IP des Brokers muss hier natürlich angepasst werden. Die Ausgabe sieht dann so aus (verkürzt):<br>
    <pre>
    solaranzeige/solaredge/wattstundengesamtheute 53.98
    solaranzeige/solaredge/transaction 0001
    solaranzeige/solaredge/protocol 0000
    solaranzeige/solaredge/laenge 00f7
    solaranzeige/solaredge/adresse 01
    solaranzeige/solaredge/befehl 03
    solaranzeige/solaredge/laenge_speicheradresse f4</pre>
    Wichtig ist, dass jeder Wert nur einmal vorkommt.
    <h2>MQTT Daten in den Rechner eingeben und ggf. anpassen</h2>
    Diesen Daten kopierst du nun komplett in den <a href="calculator.php">Rechner</a> und passt sie ggf. an.
    <h2>Homeassistant Konfiguration</h2>
    Du bekommst nun eine Konfiguration für den Homeassistant. Diese kopierst du ans Ende der <a href="https://www.home-assistant.io/docs/configuration/">configuration.yaml</a>.
</div>
<?php require_once("footer.php");?>
