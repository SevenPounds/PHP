<?php

/**
 * PHP SDK For iFLYTEK
 *
 * @version         1.0.0.0
 * @author          IFLYTEK Education Division Architecture Team
 * @copyright       © 2013, iFlyTEK CO.Ltd. All rights reserved.
 * @history
 *                  1.0.0.0 | shenghe | 2013-04-12 17:31:24 | created
 */

if (isset($_REQUEST['code'])) {
    $params = array("code" => $_REQUEST['code']);
    if (isset($_REQUEST['state'])) {
        $params["state"] = $_REQUEST['state'];
    }

    echo json_encode($params);
}


?>