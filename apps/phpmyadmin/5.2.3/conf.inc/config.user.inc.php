<?php

for ($i = 1; isset($hosts[$i - 1]); $i++) {
    $cfg['Servers'][$i]['hide_db'] = 'information_schema|performance_schema';
}
