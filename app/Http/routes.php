<?php

    /*
    |--------------------------------------------------------------------------
    | Application Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register all of the routes for an application.
    | It's a breeze. Simply tell Laravel the URIs it should respond to
    | and give it the controller to call when that URI is requested.
    |
    */

   /**
    *--------------------------------------------------------------------------
    *   Routes for laravel login
    * -------------------------------------------------------------------------
    */

use App\Http\Controllers\Auth\AuthController;

Route::get('/login', [AuthController::class, 'showLogin']);

Route::group(['middleware' => 'web'], function () {
    // your routes here

        /*Route::get('/', [
        'uses' => 'Auth\AuthController@showLoginForm',
        'name' => '/'
        ]);*/

        Route::get('/home', 'HomeController@index');
        Route::get('/home/list', 'HomeController@getData');

        Route::get('/session-timed-out', function() {
            return view('session_timed_out');
        });

        /*WBS*/
            Route::get('/materialreceiving', [
                'uses' => 'WBS\WBSMaterialReceivingController@getWBSMaterialReceiving',
                'name' => 'materialreceiving'
            ]);
            Route::get('/getReceivingSupplier', [
                'uses' => 'WBS\WBSMaterialReceivingController@getReceivingSupplier',
                'name' => 'getReceivingSupplier'
            ]);
            Route::get('/wbsmrnumber', [
                'uses' => 'WBS\WBSMaterialReceivingController@getWBSMRnumber',
                'name' => 'wbsmrnumber'
            ]);
            Route::get('/wbsmr-needs-modify-item', [
                'uses' => 'WBS\WBSMaterialReceivingController@showNeedsModificationItem',
                'name' => 'wbsmr-needs-modify-item'     
            ]);
            Route::get('/wbsmrsearch', [
                'uses' => 'WBS\WBSMaterialReceivingController@getWBSMRsearch',
                'name' => 'wbsmrsearch'
            ]);
            Route::get('/wbsmrprintmr', [
                'uses' => 'WBS\WBSMaterialReceivingController@printMaterialReceive',
                'name' => 'wbsmrprintmr'
            ]);
            Route::get('/wbsmrprintiqc', [
                'uses' => 'WBS\WBSMaterialReceivingController@printForIQC',
                'name' => 'wbsmrprintiqc'
            ]);
            Route::get('/wbsmr-refresh', [
                'uses' => 'WBS\WBSMaterialReceivingController@refeshInvoice',
                'name' => 'wbsmr-refresh'
            ]);
            Route::get('/wbsmrgetitems', [
                'uses' => 'WBS\WBSMaterialReceivingController@getItems',
                'name' => 'wbsmrgetitems'
            ]);
            Route::get('/wbsmrgetitemdata', [
                'uses' => 'WBS\WBSMaterialReceivingController@getItemData',
                'name' => 'wbsmrgetitemdata'
            ]);
            Route::get('/wbsmrcheckifnotforiqc', [
                'uses' => 'WBS\WBSMaterialReceivingController@notForIQC',
                'name' => 'wbsmrcheckifnotforiqc'
            ]);
            Route::get('/getlocation', [
                'uses' => 'WBS\WBSMaterialReceivingController@getLocation',
                'name' => 'getlocation'     
            ]);
            Route::get('/wbsmrsinglebatchitem', [
                'uses' => 'WBS\WBSMaterialReceivingController@getSingleBatchItem',
                'name' => 'wbsmrsinglebatchitem'
            ]);
            Route::get('/wbsmrgetpackage', [
                'uses' => 'WBS\WBSMaterialReceivingController@getPackage',
                'name' => 'wbsmrgetpackage'
            ]);
            Route::get('/wbsmrlookitem', [
                'uses' => 'WBS\WBSMaterialReceivingController@getWBSMRlookItem',
                'name' => 'wbsmrlookitem'
            ]);

            //Lumang routes di nagagamit
            Route::get('/wbsmrcancel', [
                'uses' => 'WBS\WBSMaterialReceivingController@wbsCancel',
                'name' => 'wbsmrcancel'
            ]);
            Route::get('/wbsmrupdateisprinted', [
                'uses' => 'WBS\WBSMaterialReceivingController@getUpdateIsPrintedview',
                'name' => 'wbsmrupdateisprinted'
            ]);
            Route::get('/wbsmrcancel', [
                'uses' => 'WBS\WBSMaterialReceivingController@wbsCancel',
                'name' => 'wbsmrcancel'
            ]);


            Route::post('/wbsmr-modify-item', [
                'uses' => 'WBS\WBSMaterialReceivingController@ModifyItem',
                'name' => 'wbsmr-modify-item'     
            ]);
            Route::post('/wbsmr-no-need-modify-item', [
                'uses' => 'WBS\WBSMaterialReceivingController@removeModificationItem',
                'name' => 'wbsmr-no-need-modify-item'     
            ]);
            Route::post('/wbsmrcanvelinvoice', [
                'uses' => 'WBS\WBSMaterialReceivingController@postCancelMr',
                'name' => 'wbsmrcanvelinvoice'
            ]);
            Route::post('/wbsmrdeletebatch', [
                'uses' => 'WBS\WBSMaterialReceivingController@postDeleteBatchItem',
                'name' => 'wbsmrdeletebatch'
            ]);
            Route::post('/wbsmrsave', [
                'uses' => 'WBS\WBSMaterialReceivingController@postSaveMaterialReceiving',
                'name' => 'wbsmrsave'
            ]);
            Route::post('/wbsmrpostinvoicenum', [
                'uses' => 'WBS\WBSMaterialReceivingController@postInvoiceNo',
                'name' => 'wbsmrpostinvoicenum'
            ]);
            Route::post('/wbsuploadbatchitems', [
                'uses' => 'WBS\WBSMaterialReceivingController@BatchItemExcel',
                'name' => 'wbsuploadbatchitems'
            ]);
            Route::post('/wbsmrprintbarcode', [
                'uses' => 'WBS\WBSMaterialReceivingController@printBarcode',
                'name' => 'wbsmrprintbarcode'
            ]);
            Route::post('/wbsmrreceiveall', [
                'uses' => 'WBS\WBSMaterialReceivingController@receiveAll',
                'name' => 'wbsmrreceiveall'
            ]);

        /*WBS LOCAL MATERIAL RECEIVING*/
            Route::get('/wbslocmat', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@index',
                'name' => 'wbslocmat'
            ]);

            Route::post('/wbsuploadlocmat', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@ExtractExcelFile',
                'name' => 'wbsuploadlocmat'
            ]);

            Route::post('/savelocamat', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@postSaveLocRec',
                'name' => 'savelocamat'
            ]);

            Route::get('/wbslocmatgetdata', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@getLocalMaterialData',
                'name' => 'wbslocmatgetdata'
            ]);

            Route::get('/wbsmrlocalprintiqc', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@printForIQC',
                'name' => 'wbsmrlocalprintiqc'
            ]);

            Route::get('/wbslocmatsummaryreport', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@summaryReport',
                'name' => 'wbslocmatsummaryreport'
            ]);

            Route::post('/wbslocalprintbarcode', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@printBarcode',
                'name' => 'wbslocalprintbarcode'
            ]);

            Route::post('/wbslocupdatebatchitem', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@updateBatchItem',
                'name' => 'wbslocupdatebatchitem'
            ]);

            Route::get('/wbslociqc', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@ApplicationForIQC',
                'name' => 'wbslociqc'
            ]);

            Route::get('/wbslocpackagecategory', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@getPackage',
                'name' => 'wbslocpackagecategory'
            ]);

            Route::post('/wbslocaldeletebatchitem', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@postDeleteBatchItem',
                'name' => 'wbslocaldeletebatchitem'
            ]);

            Route::post('/local-receiving-search', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@Search',
                'name' => 'local-receiving-search'
            ]);

            Route::get('/wbslocgettotal', [
                'uses' => 'WBS\WBSLocalMaterialReceivingController@getTotal',
                'name' => 'wbslocgettotal'
            ]);


        /*WBS IQC Inspection*/
            Route::get('/iqc', [
                'uses' => 'WBS\WBSIqcController@getWbsIqc',
                'name' => 'iqc'
            ]);

            Route::get('/getwbsiqc', [
                'uses' => 'WBS\WBSIqcController@getLoadwbs',
                'name' => 'getwbsiqc'
            ]);

            Route::get('/getwbsiqcsearch', [
                'uses' => 'WBS\WBSIqcController@getSearch',
                'name' => 'getwbsiqcsearch'
            ]);

            Route::post('/postwbsiqcsingleupdate', [
                'uses' => 'WBS\WBSIqcController@postUpdateIQCstatus',
                'name' => 'postwbsiqcsingleupdate'
            ]);

            Route::post('/postwbsiqcupdatebulk', [
                'uses' => 'WBS\WBSIqcController@postUpdateIQCstatusBulk',
                'name' => 'postwbsiqcupdatebulk'
            ]);

            // Route::get('/iqc', [
            //     'uses' => 'WBS\WBSIqcController@getWbsIqc',
            //     'name' => 'iqc'
            // ]);

            // Route::get('/getwbsiqc/{status}', [
            //     'uses' => 'WBS\WBSIqcController@getLoadwbs',
            //     'name' => 'getwbsiqc'
            // ]);

            // Route::get('/getwbsiqcsearch', [
            //     'uses' => 'WBS\WBSIqcController@getSearch',
            //     'name' => 'getwbsiqcsearch'
            // ]);

            // Route::post('/postwbsiqcsingleupdate', [
            //     'uses' => 'WBS\WBSIqcController@postUpdateIQCstatus',
            //     'name' => 'postwbsiqcsingleupdate'
            // ]);

            // Route::post('/postwbsiqcupdatebulk', [
            //     'uses' => 'WBS\WBSIqcController@postUpdateIQCstatusBulk',
            //     'name' => 'postwbsiqcupdatebulk'
            // ]);

        /*Material Kitting*/
            Route::group(['prefix' => 'material-kitting'], function() {
                Route::get('/','WBS\WBSMaterialKittingController@index')->middleware('auth','revalidate');

                Route::get('/wbsmaterialkittingdata', [
                    'uses' => 'WBS\WBSMaterialKittingController@getMaterialKittingData',
                    'name' => 'wbsmaterialkittingdata'
                ]);

                Route::post('/searchpo', [
                    'uses' => 'WBS\WBSMaterialKittingController@postSearchPO',
                    'name' => 'searchpo'
                ]);

                Route::post('/searchpoprod', [
                    'uses' => 'WBS\WBSMaterialKittingController@postSearchPOprod',
                    'name' => 'searchpoprod'
                ]);

                Route::post('/item-lot-fifo', [
                    'uses' => 'WBS\WBSMaterialKittingController@getItemAndLotnumFifo',
                    'name' => 'item-lot-fifo'
                ]);

                Route::post('/getlotnodetails', [
                    'uses' => 'WBS\WBSMaterialKittingController@postLotNoDetails',
                    'name' => 'getlotnodetails'
                ]);

                Route::post('/updatekitqty', [
                    'uses' => 'WBS\WBSMaterialKittingController@postUpdateKitQty',
                    'name' => 'updatekitqty'
                ]);

                Route::post('/savekitdetails', [
                    'uses' => 'WBS\WBSMaterialKittingController@postSaveKitDetails',
                    'name' => 'savekitdetails'
                ]);
                Route::post('/save-receivedby', [
                    'uses' => 'WBS\WBSMaterialKittingController@saveReceivedBy',
                    'name' => 'save-receivedby'
                ]);
                // Route::post('/saveissdetails', [
                //     'uses' => 'WBS\WBSMaterialKittingController@postUpdateIssuanceDetails',
                //     'name' => 'saveissdetails'
                // ]);

                Route::get('/kitdata', [
                    'uses' => 'WBS\WBSMaterialKittingController@getKitData',
                    'name' => 'kitdata'
                ]);

                Route::get('/check-kitdetails', [
                    'uses' => 'WBS\WBSMaterialKittingController@CheckDetails',
                    'name' => 'check-kitdetails'
                ]);

                // Route::get('/check-kitdetails', [
                //     'uses' => 'WBS\WBSMaterialKittingController@CheckDetails',
                //     'name' => 'check-kitdetails'
                // ]);

                Route::post('/delete-wrongdetails', [
                'uses' => 'WBS\WBSMaterialKittingController@DeleteWrongDetails',
                'name' => 'delete-wrongdetails'
                ]);
               
                Route::post('/savematissue', [
                    'uses' => 'WBS\WBSMaterialKittingController@postSaveMatIss',
                    'name' => 'savematissue'
                ]);

                Route::post('/cancel-po', [
                    'uses' => 'WBS\WBSMaterialKittingController@postCancelMatKit',
                    'name' => 'cancel-po'
                ]);

                Route::get('/search-filter', [
                    'uses' => 'WBS\WBSMaterialKittingController@searchKitData',
                    'name' => 'search-filter'
                ]);

                Route::get('/kitting-list', [
                    'uses' => 'WBS\WBSMaterialKittingController@kittingList',
                    'name' => 'kitting-list'
                ]);


                Route::get('/transfer-slip', [
                    'uses' => 'WBS\WBSMaterialKittingController@transferSlip',
                    'name' => 'transfer-slip'
                ]);

                Route::get('/wbsmatkitexceldispatch', [
                    'uses' => 'WBS\WBSMaterialKittingController@excelMkReport',
                    'name' => 'wbsmatkitexceldispatch'
                ]);

                Route::post('/transfertosakidashi', [
                    'uses' => 'WBS\WBSMaterialKittingController@postTransferToSakidashi',
                    'name' => 'transfertosakidashi'
                ]);

                Route::post('/delete-kitdetails', [
                    'uses' => 'WBS\WBSMaterialKittingController@postDeleteKitDetails',
                    'name' => 'delete-kitdetails'
                ]);

                Route::post('/delete-issdetails', [
                    'uses' => 'WBS\WBSMaterialKittingController@postDeleteIssDetails',
                    'name' => 'delete-kitdetails'
                ]);

                Route::get('/wbsmatkitfifotbl', [
                    'uses' => 'WBS\WBSMaterialKittingController@getFifoTable',
                    'name' => 'wbsmatkitfifotbl'
                ]);

                Route::get('/getbarcode', [
                    'uses' => 'WBS\WBSMaterialKittingController@getbarcode',
                    'name' => 'getbarcode'
                ]);

                Route::get('/getlotno', [
                    'uses' => 'WBS\WBSMaterialKittingController@getlotno',
                    'name' => 'getlotno'
                ]);

                Route::get('/brprint', [
                    'uses' => 'WBS\WBSMaterialKittingController@printBarCode',
                    'name' => 'brprint'
                ]);

                 Route::get('/brprintperlabel', [
                    'uses' => 'WBS\WBSMaterialKittingController@printBarCodePerLabel',
                    'name' => 'brprintperlabel'
                ]);

                Route::post('/fiforeason', [
                    'uses' => 'WBS\WBSMaterialKittingController@fifoReason',
                    'name' => 'fiforeason'
                ]);

                Route::post('/enable-item', [
                    'uses' => 'WBS\WBSMaterialKittingController@enableItem',
                    'name' => 'enable-item'
                ]);

                Route::get('/reasonlogs', [
                    'uses' => 'WBS\WBSMaterialKittingController@fifoReasonExcel',
                    'name' => 'reasonlogs'
                ]);

                Route::get('/check-issued-qty', [
                    'uses' => 'WBS\WBSMaterialKittingController@checkIssuedQty',
                    'name' => 'check-issued-qty'
                ]);

                Route::post('/update-kit-disabled', [
                    'uses' => 'WBS\WBSMaterialKittingController@UpdateKitDisabled',
                    'name' => 'update-kit-disabled'
                ]);
            });

        /*Prod Material Request*/
            Route::group(['prefix' => 'wbsprodmatrequest'], function() {
                Route::get('/', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@index',
                    'name' => 'wbsprodmatrequest'
                ]);

                Route::post('/search-po', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@SearchPO',
                    'name' => 'search-po'
                ]);

                Route::get('/select-po-details', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@selectPOeDetails',
                    'name' => 'select-po-details'
                ]);

                Route::get('/get-selections', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@getSelections',
                    'name' => 'get-selections'
                ]);

                Route::post('/save', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@save',
                    'name' => 'save'
                ]);

                Route::get('/get-data', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@getData',
                    'name' => 'get-data'
                ]);

                Route::post('/acknowledge', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@acknowledge',
                    'name' => 'acknowledge'
                ]);

                Route::get('/get-pdf', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@getPDF',
                    'name' => 'get-pdf'
                ]);

                Route::post('/cancel-request', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@cancelRequest',
                    'name' => 'cancel-request'
                ]);

                Route::post('/search-request', [
                    'uses' => 'WBS\WBSProductionMaterialRequestController@searchRequest',
                    'name' => 'search-request'
                ]);
            });



            //Issuance Sheet Acknowledgement//


            //   Route::get('/Issuancesheetacknowledgement', [
            //     'uses' => 'WBS\WBSIssuanceacknowledgementController@GetIssuancesheetacknowledgement',
            //     'name' => 'Issuancesheetacknowledgement'
            // ]);












            //Issuance Sheet Acknowledgement//


        /*Sakidashi Issuance*/
            Route::group(['prefix' => 'sakidashi-issuance'], function() {
                Route::get('/', 'WBS\WBSSakidashiIssuanceController@index')->middleware('auth','revalidate');

                Route::post('/searchpo', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@searchPO',
                    'name' => 'searchpo'
                ]);

                Route::post('/wbssisave', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@saveRecord',
                    'name' => 'wbssisave'
                ]);

                Route::get('/get-latest', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@getLatest',
                    'name' => 'get-latest'
                ]);

                Route::get('/get-sakidashi-data', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@getSakisahiData',
                    'name' => 'get-sakidashi-data'
                ]);

                Route::get('/get-transcode', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@getTransCode',
                    'name' => 'get-transcode'
                ]);

                Route::get('/get-history', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@itemHistory',
                    'name' => 'get-history'
                ]);

                Route::get('/navigate', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@sakiNavigate',
                    'name' => 'navigate'
                ]);

                Route::get('/issuance-sheet', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@printSiReport',
                    'name' => 'issuance-sheet'
                ]);

                Route::post('/cancel-po', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@cancelPO',
                    'name' => 'cancel-po'
                ]);

                Route::post('/search', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@searchSiWbsData',
                    'name' => 'search'
                ]);
                Route::post('/save-receivedby', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@saveReceivedBy',
                    'name' => 'save-receivedby'
                ]);
                Route::get('/export-to-excel', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@sakiExportToExcel',
                    'name' => 'export-to-excel'
                ]);

                Route::get('/fifo', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@getFifoTable',
                    'name' => 'fifo'
                ]);

                Route::get('/checkinpo', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@checkInPO',
                    'name' => 'checkinpo'
                ]);

                Route::get('/checkinfifo', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@checkInFIFO',
                    'name' => 'checkinfifo'
                ]);

                Route::get('/print-barcode', [
                    'uses' => 'WBS\WBSSakidashiIssuanceController@BrCodePrint',
                    'name' => 'print-barcode'
                ]);
            });

        /* Material Issuance*/
            Route::group(['prefix' => 'whs-issuance'], function() {
                Route::get('/', [
                    'uses' => 'WBS\WBSWhsIssuanceController@index',
                    'name' => 'whs-issuance'
                ]);

                Route::get('/pending-requests', [
                    'uses' => 'WBS\WBSWhsIssuanceController@getPendingRequest',
                    'name' => 'pending-requests'
                ]);

                Route::get('/view-details', [
                    'uses' => 'WBS\WBSWhsIssuanceController@viewReqDetails',
                    'name' => 'view-details'
                ]);

                Route::get('/request-details', [
                    'uses' => 'WBS\WBSWhsIssuanceController@getReqDetails',
                    'name' => 'request-details'
                ]);

                Route::get('/get-inventory', [
                    'uses' => 'WBS\WBSWhsIssuanceController@getInventory',
                    'name' => 'get-inventory'
                ]);

                Route::post('/save', [
                    'uses' => 'WBS\WBSWhsIssuanceController@save',
                    'name' => 'save'
                ]);

                Route::get('/get-data', [
                    'uses' => 'WBS\WBSWhsIssuanceController@getData',
                    'name' => 'get-data'
                ]);

                Route::post('/search-request', [
                    'uses' => 'WBS\WBSWhsIssuanceController@searchIssuance',
                    'name' => 'search-request'
                ]);

                Route::get('/get-excel', [
                    'uses' => 'WBS\WBSWhsIssuanceController@exportToExcel',
                    'name' => 'get-excel'
                ]);

                Route::get('/get-pdf', [
                    'uses' => 'WBS\WBSWhsIssuanceController@exportToPDF',
                    'name' => 'get-pdf'
                ]);

                Route::get('/clean-data', [
                    'uses' => 'WBS\WBSWhsIssuanceController@cleanData',
                    'name' => 'clean-data'
                ]);

                Route::post('/cancel-issuance', [
                    'uses' => 'WBS\WBSWhsIssuanceController@cancelIssuance',
                    'name' => 'cancel-issuance'
                ]);

                Route::get('/print-barcode', [
                    'uses' => 'WBS\WBSWhsIssuanceController@printBarcode',
                    'name' => 'print-barcode'
                ]);

                Route::get('/download-barcode', [
                    'uses' => 'WBS\WBSWhsIssuanceController@downloadBarcode',
                    'name' => 'download-barcode'
                ]);
                
                Route::get('/get-total-issued-qty', [
                    'uses' => 'WBS\WBSWhsIssuanceController@getTotalIssuedQty',
                    'name' => 'get-total-issued-qty'
                ]);
                
            });

        /* Physical Inventory*/
            Route::get('/wbsphysicalinventory', [
                'uses' => 'WBS\WBSPhysicalInventoryController@getPhysicalInventory',
                'name' => 'wbsphysicalinventory'
            ]);

            Route::post('/wbsphysicalinventory', [
                'uses' => 'WBS\WBSPhysicalInventoryController@getPhysicalInventory',
                'name' => 'wbsphysicalinventory'
            ]);

            Route::post('/wbspi-cancel', [
                'uses' => 'WBS\WBSPhysicalInventoryController@cancelPiWbs',
                'name' => 'wbspi-cancel'
            ]);

            Route::post('/wbspi-save', [
                'uses' => 'WBS\WBSPhysicalInventoryController@savePiWbs',
                'name' => 'wbspi-save'
            ]);

            Route::post('/wbspi-update', [
                'uses' => 'WBS\WBSPhysicalInventoryController@updatePiWbs',
                'name' => 'wbspi-update'
            ]);

            Route::post('/wbspi-search', [
                'uses' => 'WBS\WBSPhysicalInventoryController@searchPiWbsData',
                'name' => 'wbspi-search'
            ]);

            Route::get('/wbspi-report', [
                'uses' => 'WBS\WBSPhysicalInventoryController@printPiReport',
                'name' => 'wbspi-report'
            ]);
            Route::get('/wbsPiReport_Excel', [
                'uses' => 'WBS\WBSPhysicalInventoryController@wbsPiReport_Excel',
                'name' => 'wbsPiReport_Excel'
            ]);

            Route::get('/wbsphygetbrdetails', [
                'uses' => 'WBS\WBSPhysicalInventoryController@getBRdetails',
                'name' => 'wbsphygetbrdetails'
            ]);

        // /* Parts Receiving*/
        //     Route::get('/wbspartsreceiving', [
        //         'uses' => 'WBS\WBSPartsReceivingController@getWBSPartsReceiving',
        //         'name' => 'wbspartsreceiving'
        //     ]);

            // Route::post('/wbspartsreceiving', [
            //     'uses' => 'WBS\WBSPartsReceivingController@getWBSPartsReceiving',
            //     'name' => 'wbspartsreceiving'
            // ]);

            /* Route::post('/updateTabs', [
                'uses' => 'WBS\WBSPartsReceivingController@postUpdateTabs',
                'name' => 'updateTabs'
            ]);*/

            // Route::post('/wbspat-invoice', [
            //     'uses' => 'WBS\WBSPartsReceivingController@postLoadInvoice',
            //     'name' => 'wbspat-invoice'
            // ]);

            // Route::post('/wbspat-save', [
            //     'uses' => 'WBS\WBSPartsReceivingController@savePrWbs',
            //     'name' => 'wbspat-save'
            // ]);

            // Route::post('/wbspat-update', [
            //     'uses' => 'WBS\WBSPartsReceivingController@updatePrWbs',
            //     'name' => 'wbspat-update'
            // ]);

            // Route::post('/wbspat-cancel', [
            //     'uses' => 'WBS\WBSPartsReceivingController@cancelPrWbs',
            //     'name' => 'wbspat-cancel'
            // ]);

            // Route::post('/wbspat-search', [
            //     'uses' => 'WBS\WBSPartsReceivingController@searchPrWbsData',
            //     'name' => 'wbspat-search'
            // ]);

            // Route::post('/wbspat-barcode', [
            //     'uses' => 'WBS\WBSPartsReceivingController@printMrBarcode',
            //     'name' => 'wbspat-barcode'
            // ]);

            // Route::get('/wbspat-report', [
            //     'uses' => 'WBS\WBSPartsReceivingController@printMrReport',
            //     'name' => 'wbspat-report'
            // ]);

        /* Material Disposistion */
            Route::get('/wbsmaterialdisposition', [
                'uses' => 'WBS\WBSMaterialDispositionController@index',
                'name' => 'wbsmaterialdisposition'
            ]);

            Route::get('/matdis-search-item', [
                'uses' => 'WBS\WBSMaterialDispositionController@search_item',
                'name' => 'matdis-search-item'
            ]);

            //bago//
            Route::post('/matdis-save-item',[

                'uses' => 'WBS\WBSMaterialDispositionController@save_item',
                'name' => 'matdis-save-item'
            ]);
             Route::get('/matdis-get-data',[

                'uses' => 'WBS\WBSMaterialDispositionController@get_item',
                'name' => 'matdis-get-data'
            ]);


             Route::get('/matdis-get-searched-materials',[

                'uses' => 'WBS\WBSMaterialDispositionController@get_searched_materials',
                'name' => 'matdis-get-searched-materials'
            ]);

             Route::get('/matdis-get-data-export',[

                'uses' => 'WBS\WBSMaterialDispositionController@get_data_export',
                'name' => 'matdis-get-data-export'
            ]);

             Route::get('/matdis-get-current-qty',[

                'uses' => 'WBS\WBSMaterialDispositionController@get_current_qty',
                'name' => 'matdis-get-current-qty'
            ]);


            Route::post('/matdis-delete-item',[
                'uses' => 'WBS\WBSMaterialDispositionController@delete_item',
                'name' => 'matdis-delete-item'
            ]);





           

            

            ///////// Dito maglalagay ng route


        /*Production Material Return*/
            Route::get('/wbsprodmatreturn', [
                'uses' => 'WBS\WBSProdMatReturnController@getProdMatReturn',
                'name' => 'wbsprodmatreturn'
            ]);

            Route::get('/get-material-return-data', [
                'uses' => 'WBS\WBSProdMatReturnController@getData',
                'name' => 'get-material-return-data'
            ]);

            Route::post('/save-material-return', [
                'uses' => 'WBS\WBSProdMatReturnController@postSaveMatReturn',
                'name' => 'save-material-return'
            ]);

            Route::get('/get-issuanceno', [
                'uses' => 'WBS\WBSProdMatReturnController@getIssuanceno',
                'name' => 'get-issuanceno'
            ]);

            Route::get('/get-item-details', [
                'uses' => 'WBS\WBSProdMatReturnController@getItemDetails',
                'name' => 'get-item-details'
            ]);

            Route::get('/wbsreturn-brprint', [
                'uses' => 'WBS\WBSProdMatReturnController@BrCodePrint',
                'name' => 'wbsreturn-brprint'
            ]);



            Route::post('/delete-item-return', [
                'uses' => 'WBS\WBSProdMatReturnController@deleteItem',
                'name' => 'delete-item-return'
            ]);

            Route::post('/search-return', [
                'uses' => 'WBS\WBSProdMatReturnController@searchReturns',
                'name' => 'search-return'
            ]);

            Route::get('/excel-return', [
                'uses' => 'WBS\WBSProdMatReturnController@printExcel',
                'name' => 'excel-return'
            ]);

            Route::post('/delete-control-return', [
                'uses' => 'WBS\WBSProdMatReturnController@deleteControlNo',
                'name' => 'delete-control-return'
            ]);

        /* WBS Report */
            Route::get('/wbsreports', [
                'uses' => 'WBS\WBSReportController@getWBSReport',
                'name' => 'wbsreports'
            ]);

            Route::get('/wbsreportmklreport', [
                'uses' => 'WBS\WBSReportController@getWBSMatKit',
                'name' => 'wbsreportmklreport'
            ]);

            Route::get('/wbsreportsakireport', [
                'uses' => 'WBS\WBSReportController@getWBSSakidashi',
                'name' => 'wbsreportmklreport'
            ]);
            Route::get('/wbsreportphyreport', [
                'uses' => 'WBS\WBSReportController@wbsreportphyreport',
                'name' => 'wbsreportphyreport'
            ]);
            Route::get('/wbsreportwmireport', [
                'uses' => 'WBS\WBSReportController@wbsreportwmireport',
                'name' => 'wbsreportwmireport'
            ]);
            Route::get('/wbsreportpmrreport', [
                'uses' => 'WBS\WBSReportController@wbsreportpmrreport',
                'name' => 'wbsreportpmrreport'
            ]);

        /*WBS Email Notification Settings*/
            Route::get('/wbsemailsettings', [
                'uses' => 'WBS\WBSEmailSettingsController@getEmailSettings',
                'name' => 'wbsemailsettings'
            ]);

            Route::get('/wbsemaildata', [
                'uses' => 'WBS\WBSEmailSettingsController@LoadData',
                'name' => 'wbsemaildata'
            ]);

            Route::post('/wbssaveemailsettings', [
                'uses' => 'WBS\WBSEmailSettingsController@saveEmailSettings',
                'name' => 'wbssaveemailsettings'
            ]);

            Route::post('/wbsdemaildelete', [
                'uses' => 'WBS\WBSEmailSettingsController@deleteEmail',
                'name' => 'wbsdemaildelete'
            ]);


        /* NGR MASTER*/
            Route::get('/ngr-master', [
                'uses' => 'Master\NGRMasterController@index',
                'name' => 'ngr-master'
            ]);

            Route::post('/ngr-master-save', [
                'uses' => 'Master\NGRMasterController@save',
                'name' => 'ngr-master-save'
            ]);

            Route::post('/ngr-master-delete', [
                'uses' => 'Master\NGRMasterController@delete',
                'name' => 'ngr-master-delete'
            ]);
            
            Route::get('/ngr-master/get-list', [
                'uses' => 'Master\NGRMasterController@get_list',
                'name' => 'ngr-master.get-list'
            ]);

});

    /**
     *-------------------------------------------------------------------------
     *  Routes Security
     * ------------------------------------------------------------------------
     */

        /*Change Password*/
            Route::get('/changepassword', [
                'uses' => 'Security\ChangePasswordController@getChangePassword',
                'name' => 'changepassword'
            ]);

            Route::post('/changepasswordnow', [
                'uses' => 'Security\ChangePasswordController@postChangePass',
                'name' => 'changepasswordnow'
            ]);

        /*USER GROUP*/
            // Route::get('/usergroup', [
            //     'uses' => 'Security\UserGroupController@getUserGroup',
            //     'name' => 'usergroup'
            // ]);

            // Route::post('/add-group' ,[
            //     'uses' => 'Security\UserGroupController@postAddDescription',
            //     'name' => 'add-group'
            // ]);

            // Route::post('/update-group' ,[
            //     'uses' => 'Security\UserGroupController@updatePost',
            //     'name' => 'update-group'
            // ]);

            // Route::get('/deleteAll-group' ,[
            //     'uses' => 'Security\UserGroupController@deleteAllPost',
            //     'name' => 'deleteAll-group'
            // ]);

            // Route::post('/search-group' ,[
            //     'uses' => 'Security\UserGroupController@searchPost',
            //     'name' => 'search-setting'
            // ]);

        /*WBS SETTING*/
            Route::get('/wbssetiing', [
                'uses' => 'Security\WBSSettingController@getWBSSetting',
                'name' => 'wbssetiing'
            ]);

            Route::post('/add-setting', [
                'uses' => 'Security\WBSSettingController@postAddDescription',
                'name' => 'add-setting'
            ]);

            Route::post('/update-setting', [
                'uses' => 'Security\WBSSettingController@updatePost',
                'name' => 'update-setting'
            ]);

            Route::get('/deleteAll-setting' ,[
                'uses' => 'Security\WBSSettingController@deleteAllPost',
                'name' => 'deleteAll-setting'
            ]);

            Route::post('/search-setting' ,[
                'uses' => 'Security\WBSSettingController@searchPost',
                'name' => 'search-setting'
            ]);

        /*Packing List SETTING*/
            Route::get('/plsetting', [
                'uses' => 'Security\PackingListSettingController@getPackListSetting',
                'name' => 'plsetting'
            ]);

            Route::post('/save-plsetting', [
                'uses' => 'Security\PackingListSettingController@postSave',
                'name' => 'save-plsetting'
            ]);

            Route::post('/delete-plsetting', [
                'uses' => 'Security\PackingListSettingController@postDelete',
                'name' => 'delete-plsetting'
            ]);

        /*TRANSACTION SETTING*/
            Route::get('/transactionsetting', [
                'uses' => 'Security\TransactionController@getTransetting',
                'name' => 'transactionsetting'
            ]);

            Route::post('/add-transaction', [
                'uses' => 'Security\TransactionController@postAddDescription',
                'name' => 'add-transaction'
            ]);

            Route::post('/update-transaction', [
                'uses' => 'Security\TransactionController@updatePost',
                'name' => 'update-transaction'
            ]);

            Route::get('/deleteAll-transaction' ,[
                'uses' => 'Security\TransactionController@deleteAllPost',
                'name' => 'deleteAll-transaction'
            ]);

            Route::post('/search-transaction' ,[
                'uses' => 'Security\TransactionController@searchPost',
                'name' => 'search-setting'
            ]);

        /*PACKINGLIST*/
            // Route::get('/packinglist', [
            //     'uses' => 'Security\PackingListController@getPackingList',
            //     'name' => 'packinglist'
            // ]);

            // Route::post('/add-packinglist', [
            //     'uses' => 'Security\PackingListController@addPackingList',
            //     'name' => 'add-packinglist'
            // ]);

            // Route::post('/update-packinglist', [
            //     'uses' => 'Security\PackingListController@updatePackingList',
            //     'name' => 'update-packinglist'
            // ]);
            //  Route::get('/deleteAll-packinglist' ,[
            //     'uses' => 'Security\PackingListController@deleteAllPackingList',
            //     'name' => 'deleteAll-packinglist'
            // ]);

        /*COMPANY SETTING*/
            Route::get('/companysetting', [
                'uses' => 'Security\CompanyController@getComSetting',
                'name' => 'companysetting'
            ]);

            Route::post('/update-companysetting', [
                'uses' => 'Security\CompanyController@updatePost',
                'name' => 'update-companysetting'
            ]);


      /**
     *-------------------------------------------------------------------------
     *  Routes for Nav
     * ------------------------------------------------------------------------
     */
            Route::get('/dataextract', [
                'uses' => 'Nav\ExtractCSVFileController@DataExtract',
                'name' => 'dataextract'
            ]);

             Route::get('/export-csv', [
                'uses' => 'Nav\ExtractCSVFileController@ExportCSV',
                'name' => 'export-csv'
            ]);

             Route::post('/time-setting', [
                'uses' => 'Nav\ExtractCSVFileController@TimeSetting',
                'name' => 'time-setting'
            ]);

             Route::get('/get-time', [
                'uses' => 'Nav\ExtractCSVFileController@GetTime',
                'name' => 'get-time'
            ]);

             Route::post('/update-time', [
                'uses' => 'Nav\ExtractCSVFileController@UpdateTime',
                'name' => 'update-time'
            ]);








    /**
     *-------------------------------------------------------------------------
     *  Routes for Phase 2
     * ------------------------------------------------------------------------
     */
        /*PR CHANGE*/
            Route::get('/prchange', [
                'uses' => 'Phase2\PRChangeController@getPRChange',
                'name' => 'prchange'
            ]);

            Route::post('/uploadOrigPR', [
                'uses' => 'Phase2\PRChangeController@postOrigPR',
                'name' => 'uploadOrigPR'
            ]);

            Route::post('/uploadChangePR', [
                'uses' => 'Phase2\PRChangeController@postChangePR',
                'name' => 'uploadChangePR'
            ]);

            Route::get('/download-pr-output', [
                'uses' => 'Phase2\PRChangeController@PR_output',
                'name' => 'download-pr-output'
            ]);

        /*PR BALANCE*/
            Route::get('/prbalance', [
                'uses' => 'Phase2\PRBalanceController@getPRBalance',
                'name' => 'prbalance'
            ]);

            Route::post('/prbfiles', [
                'uses' => 'Phase2\PRBalanceController@postFiles',
                'name' => 'prbfiles'
            ]);

            Route::get('/prbexcel', [
                'uses' => 'Phase2\PRBalanceController@OutputFile',
                'name' => 'prbexcel'
            ]);

        /*YPICS INVENTORY QUERY*/
            Route::get('/inventoryquery', [
                'uses' => 'Phase2\InventoryQueryController@getInventoryQuery',
                'name' => 'inventoryquery'
            ]);

            Route::get('/inventoryquerybyparts', [
                'uses' => 'Phase2\ByPartsController@getByParts',
                'name' => 'inventoryquerybyparts'
            ]);

            Route::get('/bypartsearchitem', [
                'uses' => 'Phase2\ByPartsController@postByPartshowItem',
                'name' => 'bypartsearchitem'
            ]);

            Route::get('/bypartsitems', [
                'uses' => 'Phase2\ByPartsController@getByPartsItems',
                'name' => 'bypartsitems'
            ]);

            Route::get('/inventoryquerybybom', [
                'uses' => 'Phase2\ByBOMController@getByBOM',
                'name' => 'inventoryquerybybom'
            ]);

            Route::get('/bybomsearchitems', [
                'uses' => 'Phase2\ByBOMController@getByBOMshowProdItems',
                'name' => 'bybomsearchitems'
            ]);

            Route::get('/bybomitems', [
                'uses' => 'Phase2\ByBOMController@byBOMdetails',
                'name' => 'bybomitems'
            ]);

            Route::get('/inventoryqueryrebom', [
                'uses' => 'Phase2\ReBOMController@getReBOM',
                'name' => 'inventoryqueryrebom'
            ]);

            Route::get('/rebomitems', [
                'uses' => 'Phase2\ReBOMController@postReBOMItems',
                'name' => 'rebomitems'
            ]);

            Route::post('/rebomsearchBOM', [
                'uses' => 'Phase2\ReBOMController@reBOMdetails',
                'name' => 'rebomsearchBOM'
            ]);

            Route::post('/rebomsearchprod', [
                'uses' => 'Phase2\ReBOMController@reBOMgetProduct',
                'name' => 'rebomsearchprod'
            ]);

            Route::post('/updatestock', [
                'uses' => 'Phase2\InventoryQueryController@postUpdatebtn',
                'name' => 'updatestock'
            ]);

            Route::get('/stockqueryxls', [
                'uses' => 'Phase2\InventoryQueryController@getStockQueryExcel',
                'name' => 'stockqueryxls'
            ]);

        /*PACKING LIST SYSTEM*/
            Route::get('/packinglistsystem', [
                'uses' => 'Phase2\PackingListSystemController@getPackingListSystem',
                'name' => 'packinglistsystem'
            ]);

            Route::get('/packinglistsystemtable', [
                'uses' => 'Phase2\PackingListSystemController@getPackingListDatable',
                'name' => 'packinglistsystemtable'
            ]);

            Route::post('/packinglistsystem-save', [
                'uses' => 'Phase2\PackingListSystemController@savePackingList',
                'name' => 'packinglistsystem-save'
            ]);

            Route::post('/packinglistsystem-delete', [
                'uses' => 'Phase2\PackingListSystemController@deletePackingList',
                'name' => 'packinglistsystem-delete'
            ]);

            Route::get('/packinglistdetails', [
                'uses' => 'Phase2\PackingListSystemController@getPackingListDetails',
                'name' => 'packinglistdetails'
            ]);

            Route::post('/packinglistdetails', [
                'uses' => 'Phase2\PackingListSystemController@getPackingListDetails',
                'name' => 'packinglistdetails'
            ]);

            Route::post('/packinglistdetails-search', [
                'uses' => 'Phase2\PackingListSystemController@getPorders',
                'name' => 'packinglistdetails-search'
            ]);

            Route::post('/packinglistsystem-exportxls', [
                'uses' => 'Phase2\PackingListSystemController@exportListToXls',
                'name' => 'packinglistsystem-exportxls'
            ]);

            Route::post('/packinglistsystem-exportpdf', [
                'uses' => 'Phase2\PackingListSystemController@exportListToPdf',
                'name' => 'packinglistsystem-exportpdf'
            ]);

            Route::post('/packinglistsystem-printpdf', [
                'uses' => 'Phase2\PackingListSystemController@exportListToPdfPrint',
                'name' => 'packinglistsystem-printpdf'
            ]);

        /*PACKING LIST MOLDING*/
            Route::get('/packinglistmolding', [
                'uses' => 'PackingListMoldingController@getPackingListMolding',
                'name' => 'packinglistmolding'
            ]);


        /*DESTINATION & CLASSIFICATION*/
        //     Route::get('/test', [
        //         'uses' => 'DestinationController@test',
        //         'name' => 'test'
        //     ]);

        //     Route::get('/destination', [
        //         'uses' => 'DestinationController@getDestination',
        //         'name' => 'destination'
        //     ]);


        //     Route::get('/get_prod_destination', [
        //         'uses' => 'DestinationController@display_prod_description',
        //         'name' => 'destination'
        //     ]);
        //     Route::get('/get_line_destination', [
        //         'uses' => 'DestinationController@display_line_description',
        //         'name' => 'get_line_destination'
        //     ]);

        //     Route::post('/add-description' ,[
        //         'uses' => 'DestinationController@postAddDescription',
        //         'name' => 'add-description'
        //     ]);

        //   /*  Route::get('/delete-post' ,[
        //         'uses' => 'DestinationController@deletePost',
        //         'name' => 'delete-post'
        //     ]);*/

        //     Route::get('/display-post' ,[
        //         'uses' => 'DestinationController@displayPost',
        //         'name' => 'display-post'
        //     ]);

        //     Route::get('/deleteAll-post' ,[
        //         'uses' => 'DestinationController@deleteAllPost',
        //         'name' => 'deleteAll-post'
        //     ]);
        //     Route::post('/update-post' ,[
        //         'uses' => 'DestinationController@updatePost',
        //         'name' => 'update-post'
        //     ]);
        //     Route::post('/search-post' ,[
        //         'uses' => 'DestinationController@searchPost',
        //         'name' => 'search-post'
        //     ]);



    /**
     *-------------------------------------------------------------------------
     *  Routes for Phase 3
     * ------------------------------------------------------------------------
     */

        /* IQC Inspection */
            Route::get('/iqcinspection', [
                'uses' => 'QCDB\IQCInspectionController@getIQCInspection',
                'name' => 'iqcinspection'
            ]);

            Route::get('/iqcdbgetitems', [
                'uses' => 'QCDB\IQCInspectionController@getInvoiceItems',
                'name' => 'iqcdbgetitems'
            ]);

            Route::get('/iqcdbgetlotno', [
                'uses' => 'QCDB\IQCInspectionController@getInvoiceItemLotNo',
                'name' => 'iqcdbgetlotno'
            ]);

             Route::get('/iqc-getSelect2', [
                'uses' => 'CommonController@getSelect2details',
                'name' => 'iqc-getSelect2'
            ]);

            Route::get('/iqcdbgetitemdetails', [
                'uses' => 'QCDB\IQCInspectionController@getInvoiceItemDetails',
                'name' => 'iqcdbgetitemdetails'
            ]);

            Route::get('/iqccalculatelotqty', [
                'uses' => 'QCDB\IQCInspectionController@calculateLotQty',
                'name' => 'iqccalculatelotqty'
            ]);

            Route::get('/iqcgetdropdowns', [
                'uses' => 'QCDB\IQCInspectionController@getDropdowns',
                'name' => 'iqcgetdropdowns'
            ]);

            Route::get('/iqcsamplingplan', [
                'uses' => 'QCDB\IQCInspectionController@SamplingPlan',
                'name' => 'iqcsamplingplan'
            ]);

            Route::get('/iqcshift', [
                'uses' => 'QCDB\IQCInspectionController@getShift',
                'name' => 'iqcshift'
            ]);

            Route::post('/iqcsaveinspection', [
                'uses' => 'QCDB\IQCInspectionController@saveInspection',
                'name' => 'iqcsaveinspection'
            ]);
            Route::post('/iqcspecialaccept', [
                'uses' => 'QCDB\IQCInspectionController@specialAccept',
                'name' => 'iqcspecialaccept'
            ]);
            Route::post('/iqcdbsavemodeofdefects', [
                'uses' => 'QCDB\IQCInspectionController@saveModeOfDefectsInspection',
                'name' => 'iqcdbsavemodeofdefects'
            ]);

            Route::get('/iqcdbgetmodeofdefectsinspection', [
                'uses' => 'QCDB\IQCInspectionController@getModeOfDefectsInspection',
                'name' => 'iqcdbgetmodeofdefectsinspection'
            ]);

            Route::post('/iqcdbdeletemodeofdefects', [
                'uses' => 'QCDB\IQCInspectionController@deleteModeOfDefectsInspection',
                'name' => 'iqcdbdeletemodeofdefects'
            ]);

            Route::post('/iqcdbdeleteongoing', [
                'uses' => 'QCDB\IQCInspectionController@deleteOnGoing',
                'name' => 'iqcdbdeleteongoing'
            ]);

            Route::get('/iqcdbgetiqcdata', [
                'uses' => 'QCDB\IQCInspectionController@getIQCData',
                'name' => 'iqcdbgetiqcdata'
            ]);

            Route::get('/iqcdbgetongoing', [
                'uses' => 'QCDB\IQCInspectionController@getOngoing',
                'name' => 'iqcdbgetongoing'
            ]);

            Route::post('/iqcdbdeleteinspection', [
                'uses' => 'QCDB\IQCInspectionController@deleteIQCInspection',
                'name' => 'iqcdbdeleteinspection'
            ]);

            Route::get('/iqcdbgetitemsearch', [
                'uses' => 'QCDB\IQCInspectionController@getItemsSearch',
                'name' => 'iqcdbgetitemsearch'
            ]);

            Route::get('/iqcdbsearchinspection', [
                'uses' => 'QCDB\IQCInspectionController@searchInspection',
                'name' => 'iqcdbsearchinspection'
            ]);

            Route::get('/iqcdbgetitemrequali', [
                'uses' => 'QCDB\IQCInspectionController@getItemsRequalification',
                'name' => 'iqcdbgetitemrequali'
            ]);

            Route::get('/iqcdbgetappnorequali', [
                'uses' => 'QCDB\IQCInspectionController@getAppNoRequalification',
                'name' => 'iqcdbgetappnorequali'
            ]);

            Route::get('/iqcdbgetdetailsrequali', [
                'uses' => 'QCDB\IQCInspectionController@getDetailsRequalification',
                'name' => 'iqcdbgetdetailsrequali'
            ]);

            Route::get('/iqccalculatelotqtyrequali', [
                'uses' => 'QCDB\IQCInspectionController@calculateLotQtyRequalification',
                'name' => 'iqccalculatelotqtyrequali'
            ]);

            Route::get('/iqcdbvisualinspectionrequali', [
                'uses' => 'QCDB\IQCInspectionController@visualInspectionRequalification',
                'name' => 'iqcdbvisualinspectionrequali'
            ]);

            Route::get('/iqcgetdropdownsrequali', [
                'uses' => 'QCDB\IQCInspectionController@getDropdowns',
                'name' => 'iqcgetdropdownsrequali'
            ]);

            Route::post('/iqcsaverequali', [
                'uses' => 'QCDB\IQCInspectionController@saveRequalification',
                'name' => 'iqcsaverequali'
            ]);

            Route::get('/iqcdbgetrequalidata', [
                'uses' => 'QCDB\IQCInspectionController@getRequaliData',
                'name' => 'iqcdbgetrequalidata'
            ]);

            Route::post('/iqcdbdeleterequali', [
                'uses' => 'QCDB\IQCInspectionController@deleteRequalification',
                'name' => 'iqcdbdeleterequali'
            ]);

            Route::get('/iqcdbgetmodeofdefectsrequali', [
                'uses' => 'QCDB\IQCInspectionController@getmodeOfDefectsRequaliData',
                'name' => 'iqcdbgetmodeofdefectsrequali'
            ]);

            Route::post('/iqcdbsavemodeofdefectsrq', [
                'uses' => 'QCDB\IQCInspectionController@saveModRequalification',
                'name' => 'iqcdbsavemodeofdefectsrq'
            ]);

            Route::post('/iqcdbdeletemodeofdefectsrequali', [
                'uses' => 'QCDB\IQCInspectionController@deleteModRequalification',
                'name' => 'iqcdbdeletemodeofdefectsrequali'
            ]);

            Route::get('/iqcdbgroupbygetcontent', [
                'uses' => 'QCDB\IQCInspectionController@getGroupbyContent',
                'name' => 'iqcdbgroupbygetcontent'
            ]);

            Route::get('/iqcdbgroupbytable', [
                'uses' => 'QCDB\IQCInspectionController@getGroupByTable',
                'name' => 'iqcdbgroupbytable'
            ]);

            Route::get('/iqcdbinspectionbydate', [
                'uses' => 'QCDB\IQCInspectionController@getInspectionByDate',
                'name' => 'iqcdbinspectionbydate'
            ]);

            Route::get('/iqcprintreport', [
                'uses' => 'QCDB\IQCInspectionController@getIQCreport',
                'name' => 'iqcprintreport'
            ]);

            Route::get('/iqcprintreportexcel', [
                'uses' => 'QCDB\IQCInspectionController@getIQCreportexcel',
                'name' => 'iqcprintreportexcel'
            ]);

            Route::get('/iqcinspectionsummaryexcel', [
                'uses' => 'QCDB\IQCInspectionController@getIQCSummaryReportExcel',
                'name' => 'iqcinspectionsummaryexcel'
            ]);

            Route::get('/iqcdbgethistory', [
                'uses' => 'QCDB\IQCInspectionController@searchHistory',
                'name' => 'iqcdbgethistory'
            ]);

            //20220124
            Route::get('/iqc-available-lot-numbers', [
                'uses' => 'QCDB\IQCInspectionController@getAvailableLotNumbers',
                'name' => 'iqc-available-lot-numbers'
            ]);            

            Route::post('/insert-iqc-lot-no', [
                'uses' => 'QCDB\IQCInspectionController@insertIQCLotNo',
                'name' => '/insert-iqc-lot-no'	
            ]);

            Route::post('/upload-iqc', [
                'uses' => 'QCDB\IQCInspectionController@uploadfiles',
                'name' => 'upload-iqc'
            ]);

            Route::get('/iqcgetworkweek', [
                'uses' => 'QCDB\IQCInspectionController@getWorkWeek',
                'name' => 'iqcgetworkweek'
            ]);


            Route::get('/iqc-calculate-dppm', [
                'uses' => 'QCDB\IQCGroupByController@CalculateDPPM',
                'name' => 'iqc-calculate-dppm'
            ]);



            Route::get('/iqc-groupby-values', [
                'uses' => 'QCDB\IQCGroupByController@GroupByValues',
                'name' => 'iqc-groupby-values'
            ]);

            Route::get('/iqc-num-of-defects', [
                'uses' => 'QCDB\IQCInspectionController@getNumOfDefectives',
                'name' => 'iqc-num-of-defects'
            ]);




            Route::post('/iqcsavesortingdata', [
                'uses' => 'QCDB\IQCInspectionController@postSaveSortingData',
                'name' => 'iqcsavesortingdata'
            ]);

            Route::get('/iqcgetsortingdata', [
                'uses' => 'QCDB\IQCInspectionController@getSortingData',
                'name' => 'iqcgetsortingdata'
            ]);

            Route::post('/iqcdeletesortingdata', [
                'uses' => 'QCDB\IQCInspectionController@postDeleteSortingData',
                'name' => 'iqcdeletesortingdata'
            ]);



            Route::post('/iqcsavereworkdata', [
                'uses' => 'QCDB\IQCInspectionController@postSaveReworkData',
                'name' => 'iqcsavereworkdata'
            ]);

            Route::get('/iqcgetreworkdata', [
                'uses' => 'QCDB\IQCInspectionController@getReworkData',
                'name' => 'iqcgetreworkdata'
            ]);

            Route::post('/iqcdeletereworkdata', [
                'uses' => 'QCDB\IQCInspectionController@postDeleteReworkData',
                'name' => 'iqcdeletereworkdata'
            ]);

            

            Route::post('/iqcsavertvdata', [
                'uses' => 'QCDB\IQCInspectionController@postSaveRTVData',
                'name' => 'iqcsavertvdata'
            ]);

            Route::get('/iqcgetrtvdata', [
                'uses' => 'QCDB\IQCInspectionController@getRTVData',
                'name' => 'iqcgetrtvdata'
            ]);

            Route::post('/iqcdeletertvdata', [
                'uses' => 'QCDB\IQCInspectionController@postDeleteRTVData',
                'name' => 'iqcdeletertvkdata'
            ]);



            Route::get('/iqc-groupby-dppmgroup1', [
                'uses' => 'QCDB\IQCGroupByController@dppmgroup1',
                'name' => 'iqc-groupby-dppmgroup1'
            ]);

            Route::get('/iqc-groupby-dppmgroup2', [
                'uses' => 'QCDB\IQCGroupByController@dppmgroup2',
                'name' => 'iqc-groupby-dppmgroup2'
            ]);

            Route::get('/iqc-groupby-dppmgroup3', [
                'uses' => 'QCDB\IQCGroupByController@dppmgroup3',
                'name' => 'iqc-groupby-dppmgroup3'
            ]);

            Route::get('/iqc-groupby-dppmgroup2_Details', [
                'uses' => 'QCDB\IQCGroupByController@dppmgroup2_Details',
                'name' => 'iqc-groupby-dppmgroup2_Details'
            ]);

            Route::get('/iqc-groupby-dppmgroup3_Details', [
                'uses' => 'QCDB\IQCGroupByController@dppmgroup3_Details',
                'name' => 'iqc-groupby-dppmgroup3_Details'
            ]);


        /* OQC Inspection */
            Route::get('/oqcinspection', [
                'uses' => 'QCDB\OQCInspectionController@index',
                'name' => 'oqcinspection'
            ]);

            Route::post('/getpodetails', [
                'uses' => 'QCDB\OQCInspectionController@getPOdetails',
                'name' => 'getpodetails'
            ]);

            Route::get('/oqc-datatable', [
                'uses' => 'QCDB\OQCInspectionController@OQCDataTable',
                'name' => 'oqc_datatable'
            ]);

            Route::get('/oqc-mod-datatable', [
                'uses' => 'QCDB\OQCInspectionController@ModDataTable',
                'name' => 'oqc-mod-datatable'
            ]);

            Route::post('/oqc-save-inspection', [
                'uses' => 'QCDB\OQCInspectionController@saveInspection',
                'name' => 'oqc-save-inspection'
            ]);

            Route::post('/oqc-delete-inspection', [
                'uses' => 'QCDB\OQCInspectionController@deleteInspection',
                'name' => '/oqc-delete-inspection'
            ]);

            Route::post('/oqc-delete-mod', [
                'uses' => 'QCDB\OQCInspectionController@deleteModeOfDefects',
                'name' => '/oqc-delete-mod'
            ]);

            Route::get('/oqc-workweek', [
                'uses' => 'QCDB\OQCInspectionController@getWorkWeek',
                'name' => 'oqc-workweek'
            ]);

            Route::post('/oqc-save-mod', [
                'uses' => 'QCDB\OQCInspectionController@saveModeOfDefects',
                'name' => 'oqc-save-mod'
            ]);

            Route::get('/oqc-report-checker', [
                'uses' => 'QCDB\OQCInspectionController@ReportDataCheck',
                'name' => 'oqc-report-checker'
            ]);

            Route::get('/oqc-pdf', [
                'uses' => 'QCDB\OQCInspectionController@PDFReport',
                'name' => 'oqc-pdf'
            ]);

            Route::get('/oqc-excel', [
                'uses' => 'QCDB\OQCInspectionController@ExcelReport',
                'name' => 'oqc-excel'
            ]);

            Route::get('/oqc-groupby-values', [
                'uses' => 'QCDB\OQCInspectionController@GroupByValues',
                'name' => 'oqc-groupby-values'
            ]);


            Route::get('/oqc-initiatedata', [
                'uses' => 'QCDB\OQCInspectionController@initData',
                'name' => 'oqc-initiatedata'
            ]);

            Route::get('/oqc-getprobitems', [
                'uses' => 'QCDB\OQCInspectionController@getProbeItems',
                'name' => 'oqc-getprobitems'
            ]);

            Route::get('/getprobeproduct', [
                'uses' => 'QCDB\OQCInspectionController@getProbeProduct',
                'name' => 'getprobeproduct'
            ]);

            Route::get('/get-sampling-plan', [
                'uses' => 'QCDB\OQCInspectionController@SamplingPlan',
                'name' => 'get-sampling-plan'
            ]);

            Route::get('/oqc-num-of-defects', [
                'uses' => 'QCDB\OQCInspectionController@getNumOfDefectives',
                'name' => 'oqc-num-of-defects'
            ]);


            Route::post('/oqc-calculate-dppm', [
                'uses' => 'QCDB\OQCGroupByController@CalculateDPPM',
                'name' => 'oqc-calculate-dppm'
            ]);

            Route::get('/oqc-shift', [
                'uses' => 'QCDB\OQCInspectionController@getShift',
                'name' => 'oqc-shift'
            ]);

            Route::get('/oqc-groupby-excel', [
                'uses' => 'QCDB\OQCGroupByController@GrpByExcelReport',
                'name' => 'oqc-groupby-excel'
            ]);

            Route::get('/oqc-groupby-pdf', [
                'uses' => 'QCDB\OQCGroupByController@GrpByPDFReport',
                'name' => 'oqc-groupby-pdf'
            ]);

            Route::get('/oqc-groupby-dppmgroup1', [
                'uses' => 'QCDB\OQCGroupByController@dppmgroup1',
                'name' => 'oqc-groupby-dppmgroup1'
            ]);

            Route::get('/oqc-groupby-dppmgroup2', [
                'uses' => 'QCDB\OQCGroupByController@dppmgroup2',
                'name' => 'oqc-groupby-dppmgroup2'
            ]);

            Route::get('/oqc-groupby-dppmgroup3', [
                'uses' => 'QCDB\OQCGroupByController@dppmgroup3',
                'name' => 'oqc-groupby-dppmgroup3'
            ]);

            Route::get('/oqc-groupby-dppmgroup2_Details', [
                'uses' => 'QCDB\OQCGroupByController@dppmgroup2_Details',
                'name' => 'oqc-groupby-dppmgroup2_Details'
            ]);

            Route::get('/oqc-groupby-dppmgroup3_Details', [
                'uses' => 'QCDB\OQCGroupByController@dppmgroup3_Details',
                'name' => 'oqc-groupby-dppmgroup3_Details'
            ]);

            Route::get('/oqc-serial-no', [
                'uses' => 'QCDB\OQCInspectionController@getSerialNo',
                'name' => 'oqc-serial-no'
            ]);

            Route::post('/oqc-upload-serial-no', [
                'uses' => 'QCDB\OQCInspectionController@UploadSerial',
                'name' => 'oqc-upload-serial-no'
            ]);

            Route::get('/oqc-get-defects', [
                'uses' => 'QCDB\OQCInspectionController@getDefects',
                'name' => 'oqc-get-defects'
            ]);
            Route::get('/oqc-get-probe-lots', [
                'uses' => 'QCDB\OQCInspectionController@getProbeLots',
                'name' => 'oqc-get-probe-lots'
            ]);


        /* OQC INVENTORY */
            Route::get('/oqc-inventory', [
                'uses' => 'QCDB\OQCInventoryController@index',
                'name' => 'oqc-inventory'
            ]);
            Route::get('/oqc-inventory-data', [
                'uses' => 'QCDB\OQCInventoryController@getInventory',
                'name' => 'oqc-inventory-data'
            ]);
            Route::get('/oqc-inventory-po-details', [
                'uses' => 'QCDB\OQCInventoryController@PODetails',
                'name' => 'oqc-inventory-po-details'
            ]);
            Route::get('/oqc-inventory-pdf', [
                'uses' => 'QCDB\OQCInventoryController@PDFReport',
                'name' => 'oqc-inventory-pdf'
            ]);
            Route::get('/oqc-inventory-excel', [
                'uses' => 'QCDB\OQCInventoryController@ExcelReport',
                'name' => 'oqc-inventory-excel'
            ]);
            Route::post('/oqc-inventory-save', [
                'uses' => 'QCDB\OQCInventoryController@SaveInventory',
                'name' => 'oqc-inventory-save'
            ]);
            Route::post('/oqc-inventory-delete', [
                'uses' => 'QCDB\OQCInventoryController@DeleteInventory',
                'name' => 'oqc-inventory-delete'
            ]);

            //Check Inventory Reports
            Route::get('/oqc-inventory-report-check', [
                'uses' => 'QCDB\OQCInventoryController@ReportDataCheck',
                'name' => 'oqc-inventory-report-check'
            ]);

        /* FGS */
            Route::get('/fgs', [
                'uses' => 'QCDB\FGSController@getFGS',
                'name' => 'fgs'
            ]);
            Route::get('/FGSgetrows', [
                'uses' => 'QCDB\FGSController@FGSgetrows',
                'name' => 'FGSgetrows'
            ]);

            Route::get('/fgsdata', [
                'uses' => 'QCDB\FGSController@getFGSData',
                'name' => 'fgsdata'
            ]);

            Route::post('/fgsSave', [
                'uses' => 'QCDB\FGSController@fgsSave',
                'name' => 'fgsSave'
            ]);
            Route::get('/fgsDelete', [
                'uses' => 'QCDB\FGSController@fgsDelete',
                'name' => 'fgsDelete'
            ]);
            Route::get('/getfgsYPICSrecords', [
                'uses' => 'QCDB\FGSController@getfgsYPICSrecords',
                'name' => 'getfgsYPICSrecords'
            ]);
            Route::get('/fgssearchby', [
                'uses' => 'QCDB\FGSController@searchby',
                'name' => 'fgssearchby'
            ]);
            Route::get('/fgsprintreport', [
                'uses' => 'QCDB\FGSController@getFGSreport',
                'name' => 'fgsprintreport'
            ]);
            Route::get('/fgsprintreportexcel', [
                'uses' => 'QCDB\FGSController@getFGSreportexcel',
                'name' => 'fgsprintreportexcel'
            ]);
            Route::post('/fgsdbselectgroupby1', [
                'uses' => 'QCDB\FGSController@fgsdbselectgroupby1',
                'name' => 'fgsdbselectgroupby1'
            ]);
            Route::post('/fgsdbgroupby', [
                'uses' =>'QCDB\FGSController@fgsdbgroupby',
                'name' => 'fgsdbgroupby'
            ]);



        /* Packing Inspection*/
            Route::group(['prefix' => 'packinginspection'], function() {
                
                Route::get('/', [
                    'uses' => 'QCDB\PackingInspectionController@index',
                    'name' => 'packinginspection'
                ]);

                Route::get('/get-data-inspected', [
                    'uses' => 'QCDB\PackingInspectionController@getDataInspected',
                    'name' => 'get-data-inspected'
                ]);

                Route::post('/save-inspection', [
                    'uses' => 'QCDB\PackingInspectionController@save_inspection',
                    'name' => 'save-inspection'
                ]);

                Route::post('/save-runcard', [
                    'uses' => 'QCDB\PackingInspectionController@save_runcard',
                    'name' => 'save-runcard'
                ]);

                Route::post('/save-mod', [
                    'uses' => 'QCDB\PackingInspectionController@save_mod',
                    'name' => 'save-mod'
                ]);

                Route::get('/stamp-code', [
                    'uses' => 'QCDB\PackingInspectionController@getStampCode',
                    'name' => 'stamp-code'
                ]);

                Route::get('/initdata', [
                    'uses' => 'QCDB\PackingInspectionController@initData',
                    'name' => 'initdata'
                ]);

                Route::get('/get-runcard', [
                    'uses' => 'QCDB\PackingInspectionController@getRuncard',
                    'name' => 'get-runcard'
                ]);

                Route::get('/get-mod', [
                    'uses' => 'QCDB\PackingInspectionController@getMOD',
                    'name' => 'get-mod'
                ]);

                Route::post('/delete-inspection', [
                    'uses' => 'QCDB\PackingInspectionController@delete_inspection',
                    'name' => 'delete-inspection'
                ]);

                Route::post('/delete-runcard', [
                    'uses' => 'QCDB\PackingInspectionController@delete_runcard',
                    'name' => 'delete-runcard'
                ]);

                Route::post('/delete-mod', [
                    'uses' => 'QCDB\PackingInspectionController@delete_mod',
                    'name' => 'delete-mod'
                ]);

                Route::get('/po-details', [
                    'uses' => 'QCDB\PackingInspectionController@poDetails',
                    'name' => 'po-details'
                ]);

                Route::get('/search-pdf', [
                    'uses' => 'QCDB\PackingInspectionController@search_pdf',
                    'name' => 'search-pdf'
                ]);

                Route::get('/search-excel', [
                    'uses' => 'QCDB\PackingInspectionController@search_excel',
                    'name' => 'search-excel'
                ]);
                Route::get('/search-data', [
                    'uses' => 'QCDB\PackingInspectionController@search_data',
                    'name' => 'search-data'
                ]);

                Route::get('/groupby-values', [
                    'uses' => 'QCDB\PackingInspectionController@GroupByValues',
                    'name' => 'groupby-values'
                ]);

                Route::post('/calculate-dppm', [
                    'uses' => 'QCDB\PackingInspectionController@CalculateDPPM',
                    'name' => 'calculate-dppm'
                ]);

                Route::get('/report-data-check', [
                    'uses' => 'QCDB\PackingInspectionController@ReportDataCheck',
                    'name' => 'report-data-check'
                ]);
            });

             


            // Route::get('/packinginspection-initdata', [
            //     'uses' => 'QCDB\PackingInspectionController@initData',
            //     'name' => 'packinginspection-initdata'
            // ]);

            // Route::get('/packinginspectiondata', [
            //     'uses' => 'QCDB\PackingInspectionController@getPackingInspectionData',
            //     'name' => 'packinginspectiondata'
            // ]);
            // Route::post('/packingSave', [
            //     'uses' => 'QCDB\PackingInspectionController@packingSave',
            //     'name' => 'packingSave'
            // ]);
            // Route::post('/packing_runcard_Save', [
            //     'uses' => 'QCDB\PackingInspectionController@packing_runcard_Save',
            //     'name' => 'packing_runcard_Save'
            // ]);
            // Route::get('/packing_runcard_edit', [
            //     'uses' => 'QCDB\PackingInspectionController@packing_runcard_edit',
            //     'name' => 'packing_runcard_edit'
            // ]);

            // Route::get('/packingDelete', [
            //     'uses' => 'QCDB\PackingInspectionController@packingDelete',
            //     'name' => 'packingDelete'
            // ]);
            // Route::get('/rcpackingDelete', [
            //     'uses' => 'QCDB\PackingInspectionController@rcpackingDelete',
            //     'name' => 'rcpackingDelete'
            // ]);
            // Route::get('/rcpackingEdit', [
            //     'uses' => 'QCDB\PackingInspectionController@rcpackingEdit',
            //     'name' => 'rcpackingEdit'
            // ]);

            // Route::get('/displayruncard', [
            //     'uses' => 'QCDB\PackingInspectionController@displayruncard',
            //     'name' => 'displayruncard'
            // ]);
            // Route::get('/display_runcard', [
            //     'uses' => 'QCDB\PackingInspectionController@display_runcard',
            //     'name' => 'display_runcard'
            // ]);
            // Route::get('/getpackingYPICSrecords', [
            //     'uses' => 'QCDB\PackingInspectionController@getpackingYPICSrecords',
            //     'name' => 'getpackingYPICSrecords'
            // ]);
            // Route::get('/getlot', [
            //     'uses' => 'QCDB\PackingInspectionController@getlot',
            //     'name' => 'getlot'
            // ]);
            // Route::get('/getmod', [
            //     'uses' => 'QCDB\PackingInspectionController@getmod',
            //     'name' => 'getmod'
            // ]);
            // Route::get('/packingsearchby', [
            //     'uses' => 'QCDB\PackingInspectionController@searchby',
            //     'name' => 'packingsearchby'
            // ]);
            // Route::get('/packingprintreport', [
            //     'uses' => 'QCDB\PackingInspectionController@getPACKINGreport',
            //     'name' => 'packingprintreport'
            // ]);
            // Route::get('/packingprintreportexcel', [
            //     'uses' => 'QCDB\PackingInspectionController@getPACKINGreportexcel',
            //     'name' => 'packingprintreportexcel'
            // ]);

            // Route::get('/displaypackmod', [
            //     'uses' => 'QCDB\PackingInspectionController@displaypackmod',
            //     'name' => 'displaypackmod'
            // ]);
            // Route::post('/packmod_save', [
            //     'uses' => 'QCDB\PackingInspectionController@packmod_save',
            //     'name' => 'packmod_save'
            // ]);
            // Route::get('/packmod_edit', [
            //     'uses' => 'QCDB\PackingInspectionController@packmod_edit',
            //     'name' => 'packmod_edit'
            // ]);
            // Route::get('/packmod_delete', [
            //     'uses' => 'QCDB\PackingInspectionController@packmod_delete',
            //     'name' => 'packmod_delete'
            // ]);
            // Route::get('/getTotalmod', [
            //     'uses' => 'QCDB\PackingInspectionController@getTotalmod',
            //     'name' => 'getTotalmod'
            // ]);
            // Route::get('/getTotalruncard', [
            //     'uses' => 'QCDB\PackingInspectionController@getTotalruncard',
            //     'name' => 'getTotalruncard'
            // ]);
            // Route::post('/packingselectgroupby1', [
            //     'uses' => 'QCDB\PackingInspectionController@packingselectgroupby1',
            //     'name' => 'packingselectgroupby1'
            // ]);
            // Route::post('/packingdbgroupby', [
            //     'uses' =>'QCDB\PackingInspectionController@packingdbgroupby',
            //     'name' => 'packingdbgroupby'
            // ]);

            

        /*PACKING MOLDING*/
            Route::get('/packingmolding', [
                'uses' => 'QCMLD\PackingMoldingController@getPackingMolding',
                'name' => 'packingmolding'
            ]);
            Route::get('/packmoldgetrows', [
                'uses' => 'QCMLD\PackingMoldingController@packmoldgetrows',
                'name' => 'packmoldgetrows'
            ]);

            Route::get('/packingMOLDINGdata', [
                'uses' => 'QCMLD\PackingMoldingController@getPackingInspectionDataM',
                'name' => 'packingMOLDINGdata'
            ]);

            Route::get('/packingMOLDINGprintreport', [
                'uses' => 'QCMLD\PackingMoldingController@getPackingreportM',
                'name' => 'packingMOLDINGprintreport'
            ]);
            Route::post('/packingMOLDINGSave', [
                'uses' => 'QCMLD\PackingMoldingController@packingSaveM',
                'name' => 'packingMOLDINGSave'
            ]);
            Route::post('/packingMOLDING_runcard_Save', [
                'uses' => 'QCMLD\PackingMoldingController@packing_runcard_SaveM',
                'name' => 'packingMOLDING_runcard_Save'
            ]);
            Route::get('/packingMOLDING_runcard_edit', [
                'uses' => 'QCMLD\PackingMoldingController@packing_runcard_editM',
                'name' => 'packingMOLDING_runcard_edit'
            ]);

            Route::get('/packingMOLDINGDelete', [
                'uses' => 'QCMLD\PackingMoldingController@packingDeleteM',
                'name' => 'packingMOLDINGDelete'
            ]);
            Route::get('/rcpackingMOLDINGDelete', [
                'uses' => 'QCMLD\PackingMoldingController@rcpackingDeleteM',
                'name' => 'rcpackingMOLDINGDelete'
            ]);
            Route::get('/rcpackingMOLDINGEdit', [
                'uses' => 'QCMLD\PackingMoldingController@rcpackingEditM',
                'name' => 'rcpackingMOLDINGEdit'
            ]);

            Route::get('/displayMOLDINGruncard', [
                'uses' => 'QCMLD\PackingMoldingController@displayruncardM',
                'name' => 'displayMOLDINGruncard'
            ]);
            Route::get('/displayMOLDING_runcard', [
                'uses' => 'QCMLD\PackingMoldingController@display_runcardM',
                'name' => 'displayMOLDING_runcard'
            ]);
            Route::get('/getpackingMOLDINGYPICSrecords', [
                'uses' => 'QCMLD\PackingMoldingController@getpackingYPICSrecordsM',
                'name' => 'getpackingMOLDINGYPICSrecords'
            ]);
            Route::get('/getMOLDINGlot', [
                'uses' => 'QCMLD\PackingMoldingController@getlotM',
                'name' => 'getMOLDINGlot'
            ]);
            Route::get('/getMOLDINGmod', [
                'uses' => 'QCMLD\PackingMoldingController@getmodM',
                'name' => 'getMOLDINGmod'
            ]);
            Route::get('/packingMOLDINGsearchby', [
                'uses' => 'QCMLD\PackingMoldingController@searchbyM',
                'name' => 'packingMOLDINGsearchby'
            ]);
            Route::get('/packingMOLDINGprintreport', [
                'uses' => 'QCMLD\PackingMoldingController@getPACKINGreportM',
                'name' => 'packingMOLDINGprintreport'
            ]);
            Route::get('/packingMOLDINGprintreportexcel', [
                'uses' => 'QCMLD\PackingMoldingController@getPACKINGreportexcelM',
                'name' => 'packingMOLDINGprintreportexcel'
            ]);

            Route::get('/displayMOLDINGpackmod', [
                'uses' => 'QCMLD\PackingMoldingController@displaypackmodM',
                'name' => 'displayMOLDINGpackmod'
            ]);
            Route::post('/packMOLDINGmod_save', [
                'uses' => 'QCMLD\PackingMoldingController@packmod_saveM',
                'name' => 'packMOLDINGmod_save'
            ]);
            Route::get('/packMOLDINGmod_edit', [
                'uses' => 'QCMLD\PackingMoldingController@packmod_editM',
                'name' => 'packMOLDINGmod_edit'
            ]);
            Route::get('/packMOLDINGmod_delete', [
                'uses' => 'QCMLD\PackingMoldingController@packmod_deleteM',
                'name' => 'packMOLDINGmod_delete'
            ]);
            Route::get('/getTotalMOLDINGmod', [
                'uses' => 'QCMLD\PackingMoldingController@getTotalmodM',
                'name' => 'getTotalMOLDINGmod'
            ]);
            Route::get('/getTotalMOLDINGruncard', [
                'uses' => 'QCMLD\PackingMoldingController@getTotalruncardM',
                'name' => 'getTotalMOLDINGruncard'
            ]);
            Route::post('/packingMOLDINGselectgroupby1', [
                'uses' => 'QCMLD\PackingMoldingController@packingselectgroupby1M',
                'name' => 'packingMOLDINGselectgroupby1'
            ]);
            Route::post('/packingMOLDINGdbgroupby', [
                'uses' =>'QCMLD\PackingMoldingController@packingdbgroupbyM',
                'name' => 'packingMOLDINGdbgroupby'
            ]);
            Route::get('/packingsearchbyM', [
                'uses' => 'QCMLD\PackingMoldingController@searchbyM',
                'name' => 'packingsearchbyM'
            ]);

        /*YPICS INVOICING*/
            Route::get('/ypicsinvoicing', [
                'uses' => 'Phase3\YPICSInvoicingController@getInvoicing',
                'name' => 'ypicsinvoicing'
            ]);

            Route::get('/tblsummarydata', [
                'uses' => 'Phase3\YPICSInvoicingController@postSummaryData',
                'name' => 'tblsummarydata'
            ]);

            Route::get('/getpackinglistdatatable', [
                'uses' => 'Phase3\YPICSInvoicingController@getPackingListData',
                'name' => 'getpackinglistdatatable'
            ]);

            Route::get('/getinvoicedatatable', [
                'uses' => 'Phase3\YPICSInvoicingController@getInvoiceData',
                'name' => 'getinvoicedatatable'
            ]);

            Route::get('/detailsypicsinvoicing/{ctrl}', [
                'uses' => 'Phase3\YPICSInvoicingController@getDetailsInvoicing',
                'name' => 'detailsypicsinvoicing'
            ]);

            Route::post('/deleteinvoicedetails', [
                'uses' => 'Phase3\YPICSInvoicingController@deleteInvoice',
                'name' => 'deleteinvoicedetails'
            ]);

            Route::get('/getdetails', [
                'uses' => 'Phase3\YPICSInvoicingController@getDetails',
                'name' => 'getdetails'
            ]);

            Route::get('/getdetailsbyproduct', [
                'uses' => 'Phase3\YPICSInvoicingController@getDetailsByProduct',
                'name' => 'getdetailsbyproduct'
            ]);

            Route::get('/getinitdetails', [
                'uses' => 'Phase3\YPICSInvoicingController@getInitDetails',
                'name' => 'getinitdetails'
            ]);

            Route::get('/getncv', [
                'uses' => 'Phase3\YPICSInvoicingController@getNCV',
                'name' => 'getncv'
            ]);

            Route::get('/getcarrier', [
                'uses' => 'Phase3\YPICSInvoicingController@carrier',
                'name' => 'getcarrier'
            ]);

            Route::get('/getdescgoods', [
                'uses' => 'Phase3\YPICSInvoicingController@descOfGoods',
                'name' => 'getdescgoods'
            ]);

            Route::get('/getpod', [
                'uses' => 'Phase3\YPICSInvoicingController@portOfDestination',
                'name' => 'getpod'
            ]);

            Route::post('/savedetails', [
                'uses' => 'Phase3\YPICSInvoicingController@postSaveDetails',
                'name' => 'savedetails'
            ]);

            Route::get('/printinvoicing/{ctrl}', [
                'uses' => 'Phase3\YPICSInvoicingController@getPrintOut',
                'name' => 'printinvoicing'
            ]);

            Route::get('/invoicesummary', [
                'uses' => 'Phase3\YPICSInvoicingController@getInvoiceSummary',
                'name' => 'invoicesummary'
            ]);

            Route::get('/shippinglist', [
                'uses' => 'Phase3\YPICSInvoicingController@getShippingList',
                'name' => 'shippinglist'
            ]);

            Route::get('/salesreport', [
                'uses' => 'Phase3\YPICSInvoicingController@getSalesReport',
                'name' => 'salesreport'
            ]);

            Route::post('/edit-draftshipment', [
                'uses' => 'Phase3\YPICSInvoicingController@editDraftShipment',
                'name' => 'edit-draftshipment'
            ]);

            Route::get('/invoicestatus', [
                'uses' => 'Phase3\YPICSInvoicingController@getInvoiceStatus',
                'name' => 'invoicestatus'
            ]);

        /* PO Registration */
            Route::post('/poregistration' ,[
                'uses' => 'Yielding\PORegistrationController@getPORegistration',
                'name' => 'poregistration'
            ]);
            Route::get('/poregistration' ,[
                'uses' => 'Yielding\PORegistrationController@getPORegistration',
                'name' => 'poregistration'
            ]);
            Route::get('/displayItem' ,[
                'uses' => 'Yielding\PORegistrationController@displayListItem',
                'name' => 'displayItem'
            ]);
            Route::post('/get-poregistration' ,[
                'uses' => 'Yielding\PORegistrationController@getporeg',
                'name' => 'get-poregistration'
            ]);
            Route::post('/add-poregistration', [
                'uses' => 'Yielding\PORegistrationController@poregistration',
                'name' => 'add-poregistration'
            ]);
            Route::post('/savedevicedetails' ,[
                'uses' => 'Yielding\PORegistrationController@savedevicedetails',
                'name' => 'savedevicedetails'
            ]);
            Route::get('/yielddropdowns', [
                'uses' => 'Yielding\PORegistrationController@getDropdowns',
                'name' => 'yielddropdowns'
            ]);
            Route::get('/getFamilyDropDown', [
                'uses' => 'Yielding\PORegistrationController@getFamilyDropDown',
                'name' => '/getFamilyDropDown'
            ]);
            Route::get('/getSeriesDropdown', [
                'uses' => 'Yielding\PORegistrationController@getSeriesDropdown',
                'name' => '/getSeriesDropdown'
            ]);
            Route::get('/getProdtypeDropdown', [
                'uses' => 'Yielding\PORegistrationController@getProdtypeDropdown',
                'name' => '/getProdtypeDropdown'
            ]);

            
            
        
            Route::get('/get-deviceregistration' ,[
                'uses' => 'Yielding\PORegistrationController@getdevicereg',
                'name' => 'get-deviceregistration'
            ]);
            Route::get('/get-seriesregistration' ,[
                'uses' => 'Yielding\PORegistrationController@getseriesreg',
                'name' => 'get-seriesregistration'
            ]);
            Route::get('/getModofDef' ,[
                'uses' => 'Yielding\PORegistrationController@getModofDef',
                'name' => 'getModofDef'
            ]);
            Route::get('/displayporeg', [
                'uses' => 'Yielding\PORegistrationController@displayporeg',
                'name' => 'displayporeg'
            ]);
            Route::get('/displaydevicereg', [
                'uses' => 'Yielding\PORegistrationController@displaydevicereg',
                'name' => 'displaydevicereg'
            ]);
            Route::get('/displayseriesreg', [
                'uses' => 'Yielding\PORegistrationController@displayseriesreg',
                'name' => 'displayseriesreg'
            ]);
            Route::get('/displaymodreg', [
                'uses' => 'Yielding\PORegistrationController@displaymodreg',
                'name' => 'displaymodreg'
            ]);
             Route::get('/CheckYpicsPO', [
                'uses' => 'Yielding\PORegistrationController@CheckYpicsPO',
                'name' => 'CheckYpicsPO'
            ]);
            Route::get('/getdevice', [
                'uses' => 'Yielding\PORegistrationController@getLoadDevice',
                'name' => 'getdevice'
            ]);
            Route::post('/updatedevice', [
                'uses' => 'Yielding\PORegistrationController@postUpdatebtn',
                'name' => 'updatedevice'
            ]);
            Route::get('/dispalytablePoReg' ,[
                'uses' => 'Yielding\PORegistrationController@dispalytablePoReg',
                'name' => 'dispalytablePoReg'
            ]);
            Route::get('/deleteporeg', [
                'uses' => 'Yielding\PORegistrationController@deleteporeg',
                'name' => 'deleteporeg'
            ]);
        /* MODE OF DEFECTS */
            Route::post('/modregistration' ,[
                'uses' => 'Yielding\MODefectsController@getMODefects',
                'name' => 'modregistration'
            ]);
            Route::get('/modregistration' ,[
                'uses' => 'Yielding\MODefectsController@getMODefects',
                'name' => 'modregistration'
            ]);
            Route::get('/getProdtypeDropdown', [
                'uses' => 'Yielding\MODefectsController@getProdtypeDropdown',
                'name' => '/getProdtypeDropdown'
            ]);
            Route::get('/getModofDef' ,[
                'uses' => 'Yielding\MODefectsController@getModofDef',
                'name' => 'getModofDef'
            ]);
            Route::get('/displaymodreg', [
                'uses' => 'Yielding\MODefectsController@displaymodreg',
                'name' => 'displaymodreg'
            ]);
          
       /* YIELD TARGET */
            Route::get('/targetyieldperformance' ,[
                'uses' => 'Yielding\YieldPerformanceYieldTargetController@getYieldTarget',
                'name' => 'targetyieldperformance'
            ]);
            Route::get('/getOutputsURL' ,[
                'uses' => 'Yielding\YieldPerformanceYieldTargetController@get_outputs',
                'name' => 'getOutputsURL'
            ]);
            Route::post('/add-targetreg', [
                'uses' => 'Yielding\YieldPerformanceYieldTargetController@targetregistration',
                'name' => 'add-targetreg'
            ]);
            Route::get('/edittargetreg' ,[
                'uses' => 'Yielding\YieldPerformanceYieldTargetController@edittargetreg',
                'name' => 'edittargetreg'
            ]);
            Route::get('/deleteAlltargetreg', [
                'uses' => 'Yielding\YieldPerformanceYieldTargetController@deletetargetreg',
                'name' => 'deleteAlltargetreg'
            ]);

        /*REPORTS--YIELD PERFORMANCE*/

            Route::get('/searchPOdetails', [
                'uses' => 'Yielding\YieldPerformanceReportController@searchPOdetails',
                'name' => 'searchPOdetails'
            ]);

            Route::get('/ReportYieldPerformance', [
                'uses' => 'Yielding\YieldPerformanceReportController@getYieldPerformanceReport',
                'name' => 'ReportYieldPerformance'
            ]);
             Route::post('/ReportYieldPerformance', [
                 'uses' => 'Yielding\YieldPerformanceReportController@getYieldPerformanceReport',
                 'name' => 'ReportYieldPerformance'
            ]);
            Route::get('/export-to-excel', [
                'uses' => 'Yielding\YieldPerformanceReportController@exportToexcel',
                'name' => 'export-to-excel'
            ]);
            Route::get('/export-to-pdf', [
                'uses' => 'Yielding\YieldPerformanceReportController@exportTopdf',
                'name' => '/export-to-pdf'
            ]);
            Route::get('/summarychart', [
                'uses' => 'Yielding\YieldPerformanceReportController@summarychart',
                'name' => '/summarychart'
            ]);
            Route::get('/summaryRpt', [
                'uses' => 'Yielding\YieldPerformanceReportController@summaryRpt',
                'name' => '/summaryRpt'
            ]);
             Route::get('/summaryREpt', [
                'uses' => 'Yielding\YieldPerformanceReportController@summaryReport',
                'name' => '/summaryREpt'
            ]);

             Route::get('/checkDataExists', [
                'uses' => 'Yielding\YieldPerformanceReportController@checkDataExists',
                'name' => '/checkDataExists'
            ]);

                 Route::get('/checKdeffectsummary', [
                'uses' => 'Yielding\YieldPerformanceReportController@checkdeffectsummary',
                'name' => '/checKdeffectsummary'
            ]);


             Route::get('/checkyieldperformancesummary', [
                'uses' => 'Yielding\YieldPerformanceReportController@checkyieldperformancesummary',
                'name' => '/checkyieldperformancesummary'
            ]);

          Route::get('/checkyieldperformancesummaryperfamily', [
                'uses' => 'Yielding\YieldPerformanceReportController@checkyieldperformancesummaryperfamily',
                'name' => '/checkyieldperformancesummaryperfamily'
            ]);         

            // checkyieldperformancesummaryperfamily
            // checkyieldperformancesummary
            // checKdeffectsummary
             
            Route::get('/defectsummaryRpt', [
                'uses' => 'Yielding\YieldPerformanceReportController@defectSummary',
                'name' => '/defectsummaryRpt'
            ]);
            Route::get('/yieldsumRpt', [
                'uses' => 'Yielding\YieldPerformanceReportController@yieldsumRpt',
                'name' => '/yieldsumRpt'
            ]);
            Route::get('/yieldsumRptpdf', [
                'uses' => 'Yielding\YieldPerformanceReportController@yieldsumRptpdf',
                'name' => '/yieldsumRptpdf'
            ]);
            Route::get('/yieldsumfamRpt', [
                'uses' => 'Yielding\YieldPerformanceReportController@yieldsumfamRpt',
                'name' => '/yieldsumfamRpt'
            ]);
            Route::get('/yieldsumfamRptpdf', [
                'uses' => 'Yielding\YieldPerformanceReportController@yieldsumfamRptpdf',
                'name' => '/yieldsumfamRptpdf'
            ]);
            Route::get('/summaryRptPdf', [
                'uses' => 'Yielding\YieldPerformanceReportController@summaryRptPdf',
                'name' => '/summaryRptPdf'
            ]);
            Route::post('/loadchart', [
                'uses' => 'Yielding\YieldPerformanceReportController@loadchart',
                'name' => '/loadchart'
            ]);
            Route::get('/getponoreg', [
                'uses' => 'Yielding\YieldPerformanceReportController@getponoreg',
                'name' => '/getponoreg'
            ]);
            Route::post('/devreg_get_series', [
                'uses' => 'Yielding\YieldPerformanceReportController@devreg_get_series',
                'name' => '/devreg_get_series'
            ]);

            Route::get('/report-records', [
                'uses' => 'Yielding\YieldPerformanceReportController@records',
                'name' => '/report-records'
            ]);

            Route::get('/getYieldTargetForReport', [
                'uses' => 'Yielding\YieldPerformanceReportController@getYieldTargetForReport',
                'name' => '/getYieldTargetForReport'
            ]);
            
        /*YIELD PERFORMANCE*/
            // Route::post('/yieldperformance', [
            //     'uses' => 'Yielding\YieldPerformanceController@getYieldPerformance',
            //     'name' => 'yieldperformance'
            // ]);
            // Route::get('/deleteYIELD', [
            //     'uses' => 'Yielding\YieldPerformanceController@deleteYIELD',
            //     'name' => 'deleteYIELD'
            // ]);
            // Route::get('/getYieldPerformanceDT', [
            //     'uses' => 'Yielding\YieldPerformanceController@getYieldPerformanceDT',
            //     'name' => 'getYieldPerformanceDT'
            // ]);
            // Route::get('/get-deviceregistration' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@getdevicereg',
            //     'name' => 'get-deviceregistration'
            // ]);
            // Route::get('/get-seriesregistration' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@getseriesreg',
            //     'name' => 'get-seriesregistration'
            // ]);
            // Route::get('/getModofDef' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@getModofDef',
            //     'name' => 'getModofDef'
            // ]);
            // Route::get('/displayporeg', [
            //     'uses' => 'Yielding\YieldPerformanceController@displayporeg',
            //     'name' => 'displayporeg'
            // ]);
            // Route::get('/displayporeg', [
            //     'uses' => 'Yielding\YieldPerformanceController@displayporeg',
            //     'name' => 'displayporeg'
            // ]);
            // Route::get('/displaydevicereg', [
            //     'uses' => 'Yielding\YieldPerformanceController@displaydevicereg',
            //     'name' => 'displaydevicereg'
            // ]);
            // Route::get('/displayseriesreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@displayseriesreg',
            //     'name' => 'displayseriesreg'
            // ]);
            // Route::get('/displaymodreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@displaymodreg',
            //     'name' => 'displaymodreg'
            // ]);
            // Route::get('/deleteAll-yieldperformance', [
            //     'uses' => 'Yielding\YieldPerformanceController@deleteAll',
            //     'name' => 'deleteAll-yieldperformance'
            // ]);
            // Route::get('/deleteporeg', [
            //     'uses' => 'Yielding\YieldPerformanceController@deleteporeg',
            //     'name' => 'deleteporeg'
            // ]);
            // Route::get('/deleteAlldevicereg', [
            //     'uses' => 'Yielding\YieldPerformanceController@deletedevicereg',
            //     'name' => 'deleteAlldevicereg'
            // ]);
            // Route::get('/deleteAllseriesreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@deleteseriesreg',
            //     'name' => 'deleteAllseriesreg'
            // ]);
            // Route::get('/deleteAllmodreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@deletemodreg',
            //     'name' => 'deleteAllmodreg'
            // ]);

            // Route::get('/deleteAlltargetreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@deletetargetreg',
            //     'name' => 'deleteAlltargetreg'
            // ]);
            // Route::get('/deleteyieldingsummary' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@deleteAllPost',
            //     'name' => 'deleteyieldingsummary'
            // ]);
            // Route::post('/update-yieldsummary' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@udpateyieldsummary',
            //     'name' => 'update-yieldsummary'
            // ]);
            // Route::post('/display-poregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@displayporegistration',
            //     'name' => 'display-poregistration'
            // ]);
            // Route::post('/add-deviceregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@deviceregistration',
            //     'name' => 'add-deviceregistration'
            // ]);
            // Route::post('/display-deviceregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@displaydeviceregistration',
            //     'name' => 'display-deviceregistration'
            // ]);

            // Route::post('/add-seriesregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@seriesregistration',
            //     'name' => 'add-seriesregistration'
            // ]);

            // Route::post('/add-modregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@modregistration',
            //     'name' => 'add-modregistration'
            // ]);
            // Route::post('/add-targetreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@targetregistration',
            //     'name' => 'add-targetreg'
            // ]);
            
            // Route::get('/GetYieldPerformanceMain', [
            //     'uses' => 'Yielding\YieldPerformanceController@GetYieldPerformanceMain',
            //     'name' => 'GetYieldPerformanceMain'
            // ]);
            // Route::get('/getTargetYield', [
            //     'uses' => 'Yielding\YieldPerformanceController@getTargetYield',
            //     'name' => 'getTargetYield'
            // ]);

            // Route::get('/editporeg' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@editporeg',
            //     'name' => 'editporeg'
            // ]);
            // Route::get('/editdevicereg' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@editdevicereg',
            //     'name' => 'editdevicereg'
            // ]);
            // Route::get('/editseriesreg' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@editseriesreg',
            //     'name' => 'editseriesreg'
            // ]);
            // Route::get('/editmodreg' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@editmodreg',
            //     'name' => 'editmodreg'
            // ]);
            // Route::get('/edittargetreg' ,[
            //     'uses' => 'Yielding\YieldPerformanceController@edittargetreg',
            //     'name' => 'edittargetreg'
            // ]);
            // Route::post('/update-poregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@updateporegistration',
            //     'name' => 'update-poregistration'
            // ]);
            // Route::post('/update-deviceregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@updatedeviceregistration',
            //     'name' => 'update-deviceregistration'
            // ]);
            // Route::post('/update-seriesregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@updateseriesregistration',
            //     'name' => 'update-seriesregistration'
            // ]);
            // Route::post('/update-modregistration', [
            //     'uses' => 'Yielding\YieldPerformanceController@updatemodregistration',
            //     'name' => 'update-modregistration'
            // ]);
            // Route::get('/export-to-excel', [
            //     'uses' => 'Yielding\YieldPerformanceController@exportToexcel',
            //     'name' => 'export-to-excel'
            // ]);
            // Route::get('/export-to-pdf', [
            //     'uses' => 'Yielding\YieldPerformanceController@exportTopdf',
            //     'name' => '/export-to-pdf'
            // ]);
            // Route::get('/summarychart', [
            //     'uses' => 'Yielding\YieldPerformanceController@summarychart',
            //     'name' => '/summarychart'
            // ]);
            // Route::get('/summaryRpt', [
            //     'uses' => 'Yielding\YieldPerformanceController@summaryRpt',
            //     'name' => '/summaryRpt'
            // ]);
            //  Route::get('/summaryREpt', [
            //     'uses' => 'Yielding\YPSummaryReportController@summaryREpt',
            //     'name' => '/summaryREpt'
            // ]);
          
            // Route::get('/defectsummaryRpt', [
            //     'uses' => 'Yielding\YPSummaryDefectController@defectsummaryRpt',
            //     'name' => '/defectsummaryRpt'
            // ]);
            
            // Route::get('/yieldsumRpt', [
            //     'uses' => 'Yielding\YPYieldPerformanceSummaryController@yieldsumRpt',
            //     'name' => '/yieldsumRpt'
            // ]);
            // Route::get('/yieldsumRptpdf', [
            //     'uses' => 'Yielding\YPYieldPerformanceSummaryController@yieldsumRptpdf',
            //     'name' => '/yieldsumRptpdf'
            // ]);
            // Route::get('/yieldsumfamRpt', [
            //     'uses' => 'Yielding\YPSummaryFamilyReportController@yieldsumfamRpt',
            //     'name' => '/yieldsumfamRpt'
            // ]);
            // Route::get('/yieldsumfamRptpdf', [
            //     'uses' => 'Yielding\YPSummaryFamilyReportController@yieldsumfamRptpdf',
            //     'name' => '/yieldsumfamRptpdf'
            // ]);
            // Route::get('/summaryRptPdf', [
            //     'uses' => 'Yielding\YieldPerformanceController@summaryRptPdf',
            //     'name' => '/summaryRptPdf'
            // ]);
            // Route::post('/loadchart', [
            //     'uses' => 'Yielding\YieldPerformanceController@loadchart',
            //     'name' => '/loadchart'
            // ]);
            // Route::get('/getponoreg', [
            //     'uses' => 'Yielding\YieldPerformanceController@getponoreg',
            //     'name' => '/getponoreg'
            // ]);
            // Route::post('/devreg_get_series', [
            //     'uses' => 'Yielding\YieldPerformanceController@devreg_get_series',
            //     'name' => '/devreg_get_series'
            // ]);



        // yieldperformance2---------------------------------------------
            Route::post('/save-yield', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@addYieldperformance',
                'name' => 'save-yield'
            ]);

            Route::get('/addnewYieldperformance', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@getYieldPerformance',
                'name' => 'addnewYieldperformance'


            //DITO LALAGAY YUNG SA REWORK YIELD PERFORMANC

            ]);
             Route::get('/ReworkYieldperformance', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetReworkYieldPerformance',
                'name' => 'ReworkYieldperformance'
            ]);



             Route::get('/GetFinalVisualInspection', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetFinalVisualInspection',
                'name' => 'GetFinalVisualInspection'
            ]);

             
             Route::get('/reworkGetPoDetails', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetPoDetailsrework',
                'name' => 'reworkGetPoDetails'
            ]);


            Route::post('/rework-yield', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@ReworkyieldPerformanceUpdate',
                'name' => 'rework-yield'
            ]);


              Route::post('/save-rework', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@addreworkYieldperformance',
                'name' => 'save-rework'
            ]);


            Route::post('/reworksearch-pono2' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@reworksearchPO',
                'name' => 'reworksearch-pono2'
            ]);

            Route::post('/reworkget_mod', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@get_mod',
                'name' => '/reworkget_mod'
            ]);

            Route::get('/reworkgetautovalue',[
                'uses' => 'Yielding\ReworkYieldPerformanceController@reworkgetautovalue',
                'name' => 'reworkgetautovalue'
            ]);

            Route::get('/reworkgetpng' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@reworkgetpng',
                'name' => 'reworkgetpng'
            ]);

            Route::get('/reworkgetmng' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@reworkgetmng',
                'name' => 'reworkgetmng'
            ]);

             Route::get('/reworksearchdisplaypya', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@searchdisplaypyarework',
                'name' => 'reworksearchdisplaypya'
            ]);

             Route::get('/reworksearchdisplaycmq', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@searchdisplaycmqrework',
                'name' => 'reworksearchdisplaycmq'
            ]);

             Route::get('/reworksearchdisplaydetails', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@searchdisplaydetailsrework',
                'name' => 'reworksearchdisplaydetails'
            ]);

             Route::get('/reworksearchdisplaysummary', [
                'uses' => 'Yielding\ReworkYieldPerformanceController@searchdisplaydetails',
                'name' => 'reworksearchdisplaysummary'
            ]);

            Route::get('/reworkdeleteAll-pono2' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@deleteAllPostrework',
                'name' => 'reworkdeleteAll-pono2'
            ]);

            Route::post('/reworksearch-yieldperformance2' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@searchrework',
                'name' => 'reworksearch-yieldperformance2'
            ]);


            Route::get('/reworkdeletepya' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@deletepyarework',
                'name' => 'reworkdeletepya'
            ]);

             Route::get('/reworkdeletecmq' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@deletecmqrework',
                'name' => 'reworkdeletecmq'
            ]);

             Route::get('/GetPONumberDetails2ndpassed' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetPONumberDetailsrework',
                'name' => 'GetPONumberDetails2ndpassed'
            ]);
             Route::get('/GetFianlVisualInspection' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetFianlVisualInspection',
                'name' => 'GetFianlVisualInspection'
            ]);

             // ]);
             Route::get('/GetModdeffects' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetModdeffects',
                'name' => 'GetModdeffects'
            ]);

             Route::get('/GetClassification' ,[
                'uses' => 'Yielding\ReworkYieldPerformanceController@GetClassification',
                'name' => 'GetClassification'
            ]);

             

                // GetModdeffects
                // GetFianlVisualInspection


             //SA LOOB

             Route::get('/Getdeffects', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@Getdeffects',
                'name' => '/Getdeffects'
            ]);
                       
            Route::get('/displaypya', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@displaypya',
                'name' => 'displaypya'
            ]);
            Route::get('/searchdisplaypya', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaypya',
                'name' => 'searchdisplaypya'
            ]);
            Route::post('/displaypya', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@displaypya',
                'name' => 'displaypya'
            ]);
            Route::get('/displaycmq', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@displaycmq',
                'name' => 'displaycmq'
            ]);
            Route::get('/searchdisplaycmq', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaycmq',
                'name' => 'searchdisplaycmq'
            ]);
            Route::post('/displaycmq', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@displaycmq',
                'name' => 'displaycmq'
            ]);
            Route::get('/searchdisplaydetails', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaydetails',
                'name' => 'searchdisplaydetails'
            ]);
            Route::get('/searchdisplaysummary', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaydetails',
                'name' => 'searchdisplaysummary'
            ]);
            Route::post('/add-yieldperformance2', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@addYieldperformance',
                'name' => 'add-yieldperformance2'
            ]);

            Route::post('/search-pono2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchPO',
                'name' => 'search-pono2'
            ]);
            Route::post('/search-yieldperformance2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@search',
                'name' => 'search-yieldperformance2'
            ]);
            Route::post('/multisearch-yieldperformance2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@multiSearch',
                'name' => 'multisearch-yieldperformance2'
            ]);
            Route::get('/deleteAll-pono2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@deleteAllPost',
                'name' => 'deleteAll-pono2'
            ]);
            Route::post('/update-yieldperformance2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@updateYieldperformance',
                'name' => 'update-yieldperformance2'
            ]);
            Route::post('/multiSearchDisplay2' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@multiSearchDisplay',
                'name' => 'multiSearchDisplay2'
            ]);

            Route::get('/get-summarylist' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@summarylist',
                'name' => 'get-summarylist'
            ]);
            Route::get('/searchdisplayMNG',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplayMNG',
                'name' => 'searchdisplayMNG'
            ]);
            Route::get('/searchdisplayPNG',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplayPNG',
                'name' => 'searchdisplayPNG'
            ]);
            Route::get('/searchdisplaytoutput',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaytoutput',
                'name' => 'searchdisplaytoutput'
            ]);
            Route::get('/searchdisplaytreject',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaytreject',
                'name' => 'searchdisplaytreject'
            ]);
            Route::get('/searchdisplayYWOMNG',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplayYWOMNG',
                'name' => 'searchdisplayYWOMNG'
            ]);
            Route::get('/searchdisplayTYIELD',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplayTYIELD',
                'name' => 'searchdisplayTYIELD'
            ]);
            Route::get('/searchdisplaydppm',[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@searchdisplaydppm',
                'name' => 'searchdisplaydppm'
            ]);
            
            Route::post('/dev_get_series', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@devreg_get_series',
                'name' => '/dev_get_series'
            ]);
            Route::post('/get_mod', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@get_mod',
                'name' => '/get_mod'
            ]);
            Route::get('/checkdetails', [
                'uses' => 'Yielding\AddnewYieldingPerformanceController@checkdetails',
                'name' => '/checkdetails'
            ]);
             Route::get('/deletecmq' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@deletecmq',
                'name' => 'deletecmq'
            ]);
            Route::get('/deletepya' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@deletepya',
                'name' => 'deletepya'
            ]);
            Route::get('/getpng' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@getpng',
                'name' => 'getpng'
            ]);
            Route::get('/getmng' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@getmng',
                'name' => 'getmng'
            ]);

            Route::get('/GetPONumberDetails' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@GetPONumberDetails',
                'name' => 'GetPONumberDetails'
            ]);

            Route::get('/getRelatedseries' ,[
                'uses' => 'Yielding\AddnewYieldingPerformanceController@getRelatedseries',
                'name' => 'getRelatedseries'
            ]);



/*-------------------------------------------------------------------/
*                 DO NOT PASTE YOUR ROUTES HERE
* ------------------------------------------------------------------*/

    /**
     * --------------------------------------------------------------------
     *  Routes for Masters
     *  -------------------------------------------------------------------
     */

        /*UserController*/
            Route::get('/usermaster', 'Master\UserController@index');
            Route::get('/usermaster/create', 'Master\UserController@create');
            Route::get('/usermaster/{id}', 'Master\UserController@edit');
            Route::post('/store', 'Master\UserController@store');
            Route::post('/destory/{id}', 'Master\UserController@destroy');
            Route::post('/update/{id}', 'Master\UserController@update');

        /*ProductlineController*/
            Route::get('/productlines', [
                'uses' => 'Master\ProductlineController@getProductline',
                'name' => 'productlines'
            ]);

            Route::post('/add-product' ,[
                'uses' => 'Master\ProductlineController@postAddProduct',
                'name' => 'add-product'
            ]);

            Route::post('/delete-product' ,[
                'uses' => 'Master\ProductlineController@postDeleteProduct',
                'name' => 'delete-product'
            ]);

            Route::post('/edit-product' ,[
                'uses' => 'Master\ProductlineController@postEditProduct',
                'name' => 'edit-product'
            ]);

        /* Supplier Master Routes*/
            Route::get('/suppliermaster', [
                'uses' => 'Master\SupplierController@getSuppliermaster',
                'name' => 'suppliermaster'
            ]);

            Route::post('/register-supplier', [
                'uses' => 'Master\SupplierController@postRegisterSupplier',
                'name' => 'register-supplier'
            ]);

            Route::post('/update-supplier', [
                'uses' => 'Master\SupplierController@postUpdateSupplier',
                'name' => 'register-supplier'
            ]);

            Route::post('/edit-supplier', [
                'uses' => 'Master\SupplierController@postEditScreen',
                'name' => 'edit-supplier'
            ]);

            Route::post('/delete-supplier', [
                'uses' => 'Master\SupplierController@postDeleteSupplier',
                'name' => 'delete-supplier'
            ]);

        /*Justification Master Routes*/
            Route::get('/justificationmaster', [
                'uses' => 'Master\JustificationController@getJustificationmaster',
                'name' => 'justificationmaster'
            ]);

            Route::post('/register-justification', [
                'uses' => 'Master\JustificationController@postRegisterJustification',
                'name' => 'register-justification'
            ]);

            Route::post('/update-justification', [
                'uses' => 'Master\JustificationController@postUpdateJustification',
                'name' => 'register-justification'
            ]);

            Route::post('/edit-justification', [
                'uses' => 'Master\JustificationController@postEditScreen',
                'name' => 'edit-justification'
            ]);

            Route::post('/delete-justification', [
                'uses' => 'Master\JustificationController@postDeleteJustification',
                'name' => 'delete-justification'
            ]);

        /* DROPDOWN MASTER*/
            Route::get('/dropdown', [
                'uses' => 'Master\DropdownController@getDropdown',
                'name' => 'dropdown'
            ]);
            Route::post('/dropdown', [
                'uses' => 'Master\DropdownController@getDropdown',
                'name' => 'dropdown'
            ]);
            Route::post('/dropdown-save', [
                'uses' => 'Master\DropdownController@postAddDropdown',
                'name' => 'dropdown-save'
            ]);
            Route::post('/dropdown-delete', [
                'uses' => 'Master\DropdownController@postDeleteDropdown',
                'name' => 'dropdown-delete'
            ]);
            Route::post('/dropdown-cat-save', [
                'uses' => 'Master\DropdownController@postAddDropdownCategory',
                'name' => 'dropdown-cat-save'
            ]);
            Route::post('/dropdown-cat-delete', [
                'uses' => 'Master\DropdownController@postDelDropdownCategory',
                'name' => 'dropdown-cat-delete'
            ]);

        /*SOLD TO*/
            Route::get('/sold-to',[
                'uses' => 'Master\SoldToController@getSold',
                'name' => 'sold-to'
            ]);

            Route::post('/add-sold' ,[
                'uses' => 'Master\SoldToController@postAddsold',
                'name' => 'add-sold'
            ]);

            Route::post('/update-sold' ,[
                'uses' => 'Master\SoldToController@updatePost',
                'name' => 'update-sold'
            ]);

            Route::get('/deleteAll-sold' ,[
                'uses' => 'Master\SoldToController@deleteAllPost',
                'name' => 'deleteAll-sold'
            ]);

            Route::post('/search-sold' ,[
                'uses' => 'Master\SoldToController@searchPost',
                'name' => 'search-sold'
            ]);

    /**
     *-------------------------------------------------------------------------
     *  Routes for Phase 1
     * ------------------------------------------------------------------------
     */


        /*MRA*/
            Route::get('/mra', [
                'uses' => 'Phase1\mraController@getMRA',
                'name' => 'mra'
            ]);

            Route::get('/mraload', [
                'uses' => 'Phase1\mraController@getMRAload',
                'name' => 'mraload'
            ]);

            Route::get('/mraPrint', [
                'uses' => 'Phase1\mraController@getMRAprint',
                'name' => 'mraPrint'
            ]);

            Route::get('/generatemra', [
                'uses' => 'Phase1\mraController@generateMRA',
                'name' => 'generatemra'
            ]);

        /*Invoice Data Check*/
            Route::get('/invoicedatacheck', [
                'uses' => 'Phase1\InvoiceDataCheckController@getInvoiceDataCheck',
                'name' => 'invoicedatacheck'
            ]);

            Route::post('/readfile', [
                'uses' => 'Phase1\InvoiceDataCheckController@postReadfile',
                'name' => 'readfile'
            ]);

            Route::get('/varianceexcel', [
                'uses' => 'Phase1\InvoiceDataCheckController@varianceExcel',
                'name' => 'varianceexcel'
            ]);

            Route::get('/nonvariancecsv', [
                'uses' => 'Phase1\InvoiceDataCheckController@nonVarianceCSV',
                'name' => 'nonvariancecsv'
            ]);

            Route::get('/overdeliverypdf', [
                'uses' => 'Phase1\InvoiceDataCheckController@OverDeliveryPdf',
                'name' => 'overdeliverypdf'
            ]);

            Route::get('/unitcostexcel', [
                'uses' => 'Phase1\InvoiceDataCheckController@UnitCostExcel',
                'name' => 'unitcostexcel'
            ]);

             Route::get('/nonvarianceexcel', [
                'uses' => 'Phase1\InvoiceDataCheckController@nonVarianceExcel',
                'name' => 'nonvarianceexcel'
            ]);

        /*Order Data Check Controller*/
            Route::get('/orderdatacheck',[
                'uses' => 'Phase1\OrderDataCheckController@getOrderDataCheck',
                'name' => 'orderdatacheck'
            ]);

            Route::post('/readfiles',[
                'uses' => 'Phase1\OrderDataCheckController@postReadFiles',
                'name' => 'readfiles'
            ]);

            Route::post('/order_data_generate_report',[
                'uses' => 'Phase1\OrderDataCheckController@postOrderDataGenPDF',
                'name' => 'order_data_generate_report'
            ]);

            Route::post('/newproductpdf',[
                'uses' => 'Phase1\OrderDataCheckController@postNewProductPDF',
                'name' => 'newproductpdf'
            ]);

            Route::post('/readExcel',[
                'uses' => 'Phase1\OrderDataCheckController@postExcel',
                'name' => 'readExcel'
            ]);

            Route::get('/umPartDNexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchPartsDN',
                'name' => 'umPartDNexcel'
            ]);

            Route::get('/umUnitexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchUnitPrice',
                'name' => 'umUnitexcel'
            ]);

            Route::get('/umSalesexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchSalePrice',
                'name' => 'umSalesexcel'
            ]);

            Route::get('/umBOMexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchBOM',
                'name' => 'umBOMexcel'
            ]);

            Route::get('/umProdDNexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchProdDN',
                'name' => 'umProdDNexcel'
            ]);

            Route::get('/umProdNameexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchProdName',
                'name' => 'umProdNameexcel'
            ]);

            Route::get('/umPartNameexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchPartName',
                'name' => 'umPartNameexcel'
            ]);

            Route::get('/umSuppexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchSupplier',
                'name' => 'umSuppexcel'
            ]);

            Route::get('/umUsageexcel',[
                'uses' => 'Phase1\OrderDataCheckController@UnmatchUsage',
                'name' => 'umUsageexcel'
            ]);

            Route::get('/momscheck',[
                'uses' => 'Phase1\OrderDataCheckController@getMomsCheckExcel',
                'name' => 'momscheck'
            ]);

        /*YPICS R3 ORDER DATA REPORT Routes*/
            // Route::get('/orderdatareport', [
            //     'uses' => 'Phase1\OrderDataReportController@getOrderDataReport',
            //     'name' => 'orderdatareport'
            // ]);

            // Route::post('/connect-orderdatareport', [
            //     'uses' => 'Phase1\OrderDataReportController@postOrderDataReport',
            //     'name' => 'connect-orderdatareport'
            // ]);

            // Route::post('/print-orderdatareport', [
            //     'uses' => 'Phase1\OrderDataReportController@printOrderDataReport',
            //     'name' => 'print-orderdatareport'
            // ]);

            // Route::post('/mrpusers-orderdatareport', [
            //     'uses' => 'Phase1\OrderDataReportController@startStopUsingYpics',
            //     'name' => 'mrpusers-orderdatareport'
            // ]);

            Route::group(['prefix' => 'ypicsr3'], function() {
                Route::get('/', 'Phase1\OrderDataReportController@index')->middleware('auth','revalidate');
                Route::post('/connect-orderdatareport', [
                    'uses' => 'Phase1\OrderDataReportController@postOrderDataReport',
                    'name' => 'connect-orderdatareport'
                ]);
                Route::post('/print-orderdatareport', [
                    'uses' => 'Phase1\OrderDataReportController@printOrderDataReport',
                    'name' => 'print-orderdatareport'
                ]);
                Route::post('/mrpusers-orderdatareport', [
                    'uses' => 'Phase1\OrderDataReportController@startStopUsingYpics',
                    'name' => 'mrpusers-orderdatareport'
                ]);
                Route::get('/ypics-user-data', 'Phase1\OrderDataReportController@getYpicsUserData');
                Route::get('/ypicsr3datatable', 'Phase1\OrderDataReportController@getYPICSR3datatable');
            });


        /*MRP Calculation*/
            Route::get('/mrpcalculation', [
                'uses' => 'Phase1\MRPCalculationController@getMRP',
                'name' => 'mrpcalculation'
            ]);

            Route::post('/mrpreadfiles', [
                'uses' => 'Phase1\MRPCalculationController@postReadFiles',
                'name' => 'mrpreadfiles'
            ]);

            Route::get('/mrpexcel', [
                'uses' => 'Phase1\MRPCalculationController@exportMrpDataToExcel',
                'name' => 'mrpexcel'
            ]);

            // Route::get('/mrpexcel', [
            //     'uses' => 'Phase1\MRPCalculationController@exportMrpData',
            //     'name' => 'mrpexcel'
            // ]);

        /*PARTS REJECTION RATE SYSTEM*/
            Route::get('/partsrejectionrate', [
                'uses' => 'Phase1\PartsRejectionRateController@getPartsRejectionRate',
                'name' => 'partsrejectionrate'
            ]);

            Route::get('/prrs-getPerioidCovered', [
                'uses' => 'Phase1\PartsRejectionRateController@getPeriodCovered',
                'name' => 'prrs-getPerioidCovered'
            ]);

            Route::post('/prrs-save', [
                'uses' => 'Phase1\PartsRejectionRateController@postPartsRejectionRate',
                'name' => 'prrs-save'
            ]);

            Route::post('/prrs-export', [
                'uses' => 'Phase1\PartsRejectionRateController@exportPartsRejectionRate',
                'name' => 'prrs-export'
            ]);

            Route::post('/prrs-uploadfile', [
                'uses' => 'Phase1\PartsRejectionRateController@postPrrsUploadFile',
                'name' => 'prrs-uploadfile'
            ]);

        /*Material List*/
            Route::get('/materiallist', [
                'uses' => 'Phase1\MaterialListController@getIndex',
                'name' => 'materiallist'
            ]);


            Route::post('/material_list_pdf', [
                'uses' => 'Phase1\MaterialListController@postGenerateMaterialList',
                'name' => 'material_list_pdf'
            ]);

            Route::get('/material-list-header/{data}', function() {
                return view('pdf.material_list_header')->with(['data'=>$data]);
            });


    /**
     *-------------------------------------------------------------------------
     *  Routes for SSS
     * ------------------------------------------------------------------------
     */

        /*SSS - PO STATUS*/
            Route::get('/postatus', [
              'uses' => 'SSS\PoStatusController@getPoStatus',
              'name' => 'postatus'
              ]);

            Route::get('/postatusajax', [
              'uses' => 'SSS\PoStatusController@ajaxPOStatus',
              'name' => 'postatusajax'
              ]);

            Route::post('/postatus', [
              'uses' => 'SSS\PoStatusController@postPoStatus',
              'name' => 'postatus'
              ]);

            Route::get('/po_printing', [
              'uses' => 'SSS\PoStatusController@printToPdf',
              'name' => 'po_printing'
              ]);

            Route::post('/po_printing', [
              'uses' => 'SSS\PoStatusController@printToPdf',
              'name' => 'po_printing'
              ]);

        /*SSS - PO PARTS STATUS*/
            Route::get('/popartsstatus', [
              'uses' => 'SSS\PoPartsStatusController@getPoPartsStatus',
              'name' => 'popartsstatus'
              ]);

            Route::post('/print-popartstatus', [
              'uses' => 'SSS\PoPartsStatusController@postPrintPoPartsStatus',
              'name' => 'print-popartstatus'
              ]);

        /*SSS - PO ISOGI INPUT*/
            Route::get('/poisogiinput', [
              'uses' => 'SSS\PoIsoGiInputController@getPoIsoGiInput',
              'name' => 'poisogiinput'
            ]);

            Route::post('/post-poisogiinput', [
              'uses' => 'SSS\PoIsoGiInputController@getPoIsoGiInput',
              'name' => 'post-poisogiinput'
              ]);

            Route::post('/print-poisogiinput', [
              'uses' => 'SSS\PoIsoGiInputController@postPrintIsoStatus',
              'name' => 'print-poisogiinput'
            ]);

        /*SSS - PO CHANGE DELIVERY*/
            Route::get('/pochangedelivery', [
              'uses' => 'SSS\PoChangeDeliveryController@getPoChangeDelivery',
              'name' => 'pochangedelivery'
            ]);

            Route::post('/send-mail', [
              'uses' => 'SSS\PoChangeDeliveryController@sendMail',
              'name' => 'send-mail'
            ]);

        /*Delivery Warning*/
            Route::get('/deliverywarning', [
                'uses' => 'SSS\DeliveryWarningController@getDeliveryWarning',
                'name' => 'deliverywarning'
            ]);

            Route::get('/getalldeliverywarning', [
                'uses' => 'SSS\DeliveryWarningController@getAllDeliveryWarning',
                'name' => 'getalldeliverywarning'
            ]);

            Route::get('/deliverywarningload', [
                'uses' => 'SSS\DeliveryWarningController@getAllDeliveryWarning',
                'name' => 'deliverywarningload'
            ]);

            Route::get('/loadDeliveryWarningWithDate', [
                'uses' => 'SSS\DeliveryWarningController@loadDeliveryWarningWithDate',
                'name' => 'loadDeliveryWarningWithDate'
            ]);

            Route::post('/postDeliveryWarningExcel', [
                'uses' => 'SSS\DeliveryWarningController@postDeliveryWarningExcel',
                'name' => 'postDeliveryWarningExcel'
            ]);

            Route::post('/postDeliveryWarningPDF', [
                'uses' => 'SSS\DeliveryWarningController@postDeliveryWarningPDF',
                'name' => 'postDeliveryWarningPDF'
            ]);

        /*Answer Input Management*/

            Route::get('/answerinputmanagement', [
                'uses' => 'SSS\AnswerInputManagementController@getIndex',
                'name' => 'answerinputmanagement'
            ]);
            Route::get('/answerinputmanagementload', [
                'uses' => 'SSS\AnswerInputManagementController@getAllAnswerInputManagement',
                'name' => 'answerinputmanagementload'
            ]);

            Route::get('/answerinputmanagementloadwithexceptions', [
                'uses' => 'SSS\AnswerInputManagementController@answerinputmanagementloadwithexceptions',
                'name' => 'answerinputmanagementloadwithexceptions'
            ]);

            Route::post('/postanswerinputmanagementexcel', [
                'uses' => 'SSS\AnswerInputManagementController@postanswerinputmanagementexcel',
                'name' => 'postanswerinputmanagementexcel'
            ]);

        /*Sample Douji Input*/
            Route::get('/sampledoujiinput', [
                'uses' => 'SSS\SampleDoujiInputController@getIndex',
                'name' => 'sampledoujiinput'
            ]);
            Route::get('/sampledoujiinputload', [
                'uses' => 'SSS\SampleDoujiInputController@getAllSampleDoujiInput',
                'name' => 'sampledoujiinputload'
            ]);

            Route::post('/doujiexportexcel', [
                'uses' => 'SSS\SampleDoujiInputController@postDoujiExportExcel',
                'name' => 'doujiexportexcel'
            ]);

        /*Parts Status*/
            Route::get('/partsstatus', [
                'uses' => 'SSS\PartsStatusController@getPartsStatus',
                'name' => 'partsstatus'
            ]);

            Route::post('/postpartstatus', [
                'uses' => 'SSS\PartsStatusController@getPartsStatus',
                'name' => 'postpartstatus'
            ]);

            Route::post('/print-partstatus', [
              'uses' => 'SSS\PartsStatusController@postPrintPartsStatus',
              'name' => 'print-partstatus'
            ]);

        /*Data Update*/
            Route::get('/dataupdate', [
                'uses' => 'SSS\DataUpdateController@getDataUpdate',
                'name' => 'dataupdate'
            ]);

            Route::post('/partsanswer', [
                'uses' => 'SSS\DataUpdateController@postPartsAnswer',
                'name' => 'partsanswer'
            ]);

            Route::post('/mrp_and_r3answer', [
                'uses' => 'SSS\DataUpdateController@post_mrp_and_r3answer',
                'name' => 'mrp_and_r3answer'
            ]);

            Route::get('/getFileDate', [
                'uses' => 'SSS\DataUpdateController@getFileDate',
                'name' => 'getFileDate'
            ]);

        /*Answer Input*/
            // Route::get('/answerinput', [
            //     'uses' => 'SSS\AnswerInputController@getAnswerInput',
            //     'name' => 'answerinput'
            // ]);

         /* FLEX SCHEDULE */
            Route::get('/ypics-flex-schedule', [
                'uses' => 'YPICS\FlexScheduleController@index',
                'name' => 'ypics-flex-schedule'
            ]);
            Route::get('/flex-process-inventory-data', [
                'uses' => 'YPICS\FlexScheduleController@processInventoryData',
                'name' => 'flex-process-inventory-data'
            ]);
            Route::get('/flex-download-inventory-data', [
                'uses' => 'YPICS\FlexScheduleController@downloadFile',
                'name' => 'flex-download-inventory-data'
            ]);
            Route::post('/flex-parts-incoming-upload', [
                'uses' => 'YPICS\FlexScheduleController@CheckPPSDeliveryFile',
                'name' => 'flex-parts-incoming-upload'
            ]);
            Route::get('/flex-parts-incoming-process', [
                'uses' => 'YPICS\FlexScheduleController@processPPSDeliveryFile',
                'name' => 'flex-parts-incoming-process'
            ]);
            Route::get('/flex-parts-incoming-download', [
                'uses' => 'YPICS\FlexScheduleController@downloadFile',
                'name' => 'flex-parts-incoming-download'
            ]);
            Route::post('/flex-prod-balance-upload', [
                'uses' => 'YPICS\FlexScheduleController@CheckZYMRFile',
                'name' => 'flex-prod-balance-upload'
            ]);
            Route::get('/flex-prod-balance-process', [
                'uses' => 'YPICS\FlexScheduleController@processZYMRFile',
                'name' => 'flex-prod-balance-process'
            ]);
            Route::get('/flex-prod-balance-download', [
                'uses' => 'YPICS\FlexScheduleController@downloadFile',
                'name' => 'flex-prod-balance-download'
            ]);


/*-------------------------------------------------------------------/
*                 DO NOT PASTE YOUR ROUTES BEYOND THIS SECTION
* ------------------------------------------------------------------*/


    Route::get('/', 'Auth\AuthController@getLogin');

    //Route::auth();

    // Authentication Routes...
    //Route::get('login', 'Auth\AuthController@showLoginForm');
    Route::post('login', 'Auth\AuthController@login');
    Route::get('logout', 'Auth\AuthController@logout');

    // Registration Routes...
    //Route::get('register', 'Auth\AuthController@showRegistrationForm');
    //Route::post('register', 'Auth\AuthController@register');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
    Route::post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\PasswordController@reset');

    //withdrawal details
    Route::get('/withdrawal-detail', 'YPICS\WithdrawalDetailController@index');
    Route::post('/xhiki-readfile', 'YPICS\WithdrawalDetailController@processExcelFile');
    Route::get('/xhiki-excel', 'YPICS\WithdrawalDetailController@downloadExcelFile');
    Route::get('/xhiki-checkdata', 'YPICS\WithdrawalDetailController@checkData');

    //ypics dispatch
    Route::get('/ypics-dispatch', 'YPICS\YPICSDispatchController@index');
    Route::post('/dispatch-readfile', 'YPICS\YPICSDispatchController@processExcelFile');
    Route::get('/dispatch-excel', 'YPICS\YPICSDispatchController@downloadExcelFile');
    Route::get('/dispatch-checkdata', 'YPICS\YPICSDispatchController@checkData');
    Route::get('/dispatch-datatable', 'YPICS\YPICSDispatchController@getDispatchData');

    //YPICS Invoicing Mark up
    Route::get('/invoicing-markup', 'Master\InvoicingMarkupController@index');
    Route::get('/invoicing-markup-show', 'Master\InvoicingMarkupController@show');
    Route::post('/invoicing-markup-store', 'Master\InvoicingMarkupController@store');
    Route::get('/invoicing-markup/{id}/edit', 'Master\InvoicingMarkupController@edit');
    Route::post('/invoicing-markup-update/{id}', 'Master\InvoicingMarkupController@update');
    Route::post('/invoicing-markup-delete', 'Master\InvoicingMarkupController@destroy');

    // IQC Matrix
    Route::get('/iqc-matrix', 'QCDB\IQCMatrixController@index');
    Route::get('/iqc-matrix-show', 'QCDB\IQCMatrixController@show');
    Route::post('/iqc-matrix-store', 'QCDB\IQCMatrixController@store');
    Route::get('/iqc-matrix-edit', 'QCDB\IQCMatrixController@edit');
    Route::post('/iqc-matrix-update/{id}', 'QCDB\IQCMatrixController@update');
    Route::post('/iqc-matrix-delete', 'QCDB\IQCMatrixController@destroy');
    Route::get('/iqc-matrix-classification', 'QCDB\IQCMatrixController@showClassification');
    Route::get('/iqc-matrix-details', 'QCDB\IQCMatrixController@getDetails');
    Route::post('/iqc-matrix-upload', 'QCDB\IQCMatrixController@ExtractExcelFile');
    Route::get('/iqc-matrix-excel', 'QCDB\IQCMatrixController@getExcelReport');

    // WBS Inventory
    Route::get('/wbs-inventory', 'WBS\WBSInventoryController@index');
    Route::get('/wbs-inventory-list', 'WBS\WBSInventoryController@inventory_list');
    Route::get('/wbs-inventory-search', 'WBS\WBSInventoryController@inventory_search');
    Route::post('/wbs-inventory-refresh', 'WBS\WBSInventoryController@refresh_inventory');
    Route::post('/wbs-inventory-delete', 'WBS\WBSInventoryController@deleteselected');
    Route::post('/wbs-inventory-save', 'WBS\WBSInventoryController@savedata');
    Route::get('/wbs-inventory-excel', 'WBS\WBSInventoryController@inventory_excel');


