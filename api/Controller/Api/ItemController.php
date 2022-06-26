<?php
/**
 * Teampass - a collaborative passwords manager.
 * ---
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ---
 *
 * @project   Teampass API
 *
 * @file      ItemControler.php
 * ---
 *
 * @author    Nils Laumaillé (nils@teampass.net)
 *
 * @copyright 2009-2022 Teampass.net
 *
 * @license   https://spdx.org/licenses/GPL-3.0-only.html#licenseText GPL-3.0
 * ---
 *
 * @see       https://www.teampass.net
 */
class ItemController extends BaseController
{
    /**
     * "/item/inFolders" Endpoint - Get list of users
     */
    public function inFoldersAction($userData)
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        // get parameters
        $arrQueryStringParams = $this->getQueryStringParams();
        //print_r($arrQueryStringParams);

        if (strtoupper($requestMethod) == 'GET') {
            // define WHERE clause
            $sqlExtra = '';
            if (is_null($userData['folders_list']) === false) {
                $userData['folders_list'] = explode(',', $userData['folders_list']);
            }

            $foldersList = '';
            if (isset($arrQueryStringParams['folders']) && $arrQueryStringParams['folders']) {
                echo $arrQueryStringParams['folders'].";;";
                print_r(json_decode($arrQueryStringParams['folders']));
                $foldersList = implode(',', array_intersect($arrQueryStringParams['folders'], $userData['folders_list']));
                $sqlExtra = ' WHERE id_tree IN ('.$foldersList.')';
            }
            if (count($foldersList) === 0) {
                $strErrorDesc = 'Folders are mandatory';
                $strErrorHeader = 'HTTP/1.1 401 Expected parameters not provided';
            }

            // send query
            try {
                $itemModel = new ItemModel();

                // SQL FOLDERS

                // SQL LIMIT
                $intLimit = 0;
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }
 
                $arrItems = $itemModel->getItems($sqlExtra, $intLimit);
                $responseData = json_encode($arrItems);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
 
        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}