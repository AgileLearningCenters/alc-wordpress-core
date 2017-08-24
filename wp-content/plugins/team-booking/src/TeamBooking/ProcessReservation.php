<?php

namespace TeamBooking;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Frontend\Components,
    TeamBooking\Database,
    TeamBooking\Frontend\Review;

/**
 * Class ProcessReservation
 *
 * @author VonStroheim
 */
class ProcessReservation
{
    /**
     * Process onsite payments
     *
     * @throws \Exception
     */
    public static function processOnsite()
    {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'teambooking_process_payment_onsite')) {
            $message = ProcessReservation::getErrorMessage(__('Please refresh the page...', 'team-booking'));
            exit(Toolkit\wrapAjaxResponse($message));
        }
        ///////////////////////
        // Reservation data  //
        ///////////////////////
        $reservation_checksum = filter_input(INPUT_POST, 'reservation_checksum');
        $reservation_database_id = filter_input(INPUT_POST, 'reservation_database_id');
        $reservation_data = Database\Reservations::getById($reservation_database_id);
        if ($reservation_data->getToken() != $reservation_checksum) {
            exit;
        }
        ///////////////////
        // Service data  //
        ///////////////////
        $service_id = $reservation_data->getServiceId();
        ////////////////////////////
        // Additional parameters  //
        ////////////////////////////
        $additional_parameter = filter_input(INPUT_POST, 'additional_parameter');
        ///////////////////
        // Gateway boot  //
        ///////////////////
        $gateway_id = filter_input(INPUT_POST, 'gateway_id');
        $response = Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->prepareGateway($reservation_data, $additional_parameter);
        if (!($response instanceof \TeamBooking_Error)) {
            /**
             * at this point, $response must be an array
             * with relevant payment details
             */
            $reservation_data->setPaid(TRUE);
            $reservation_data->setPaymentGateway($gateway_id);
            $reservation_data->setPaymentDetails($response);
            /**
             * Let's do the reservation only if it was pending,
             * and it was of course pending if the payment is
             * requested immediately
             */
            if (Database\Services::get($service_id)->getSettingsFor('payment') === 'immediately') {
                $reservation_class = new \TeamBooking_Reservation($reservation_data);
                $reservation_data = $reservation_class->doReservation();
                if ($reservation_data instanceof \TeamBooking_ReservationData) {
                    $reservation_data->setStatusConfirmed();
                    // Send e-mail messages
                    if ($reservation_class->getServiceObj()->getEmailToAdmin('send')) {
                        $reservation_class->sendNotificationEmail();
                    }
                    if (Functions\getSettings()->getCoworkerData($reservation_data->getCoworker())->getCustomEventSettings($reservation_data->getServiceId())->getGetDetailsByEmail()) {
                        $reservation_class->sendNotificationEmailToCoworker();
                    }
                    if ($reservation_data->getCustomerEmail() && $reservation_class->getServiceObj()->getEmailToCustomer('send')) {
                        $reservation_class->sendConfirmationEmail();
                    }
                }
            }
            /**
             * Let's update the database
             */
            ob_start();
            if (!$reservation_data instanceof \TeamBooking_Error) {
                Database\Reservations::update($reservation_data);
                // All is done, redirect or not?
                if (Database\Services::get($reservation_data->getServiceId())->getSettingsFor('redirect')) {
                    echo Database\Services::get($reservation_data->getServiceId())->getRedirectUrl($reservation_database_id);
                } else {
                    // start of HTML response >>>>
                    echo Components\NavigationHeader::InPaymentSuccess();
                    ?>
                    <div class="tbk-slide-body">
                        <div class="ui positive message">
                            <div class="tbk-header">
                                <?= esc_html__('Thank you!', 'team-booking') ?>
                            </div>
                            <p><?= esc_html__('Your payment was succesful!', 'team-booking') ?></p>
                        </div>
                        <?php Components\SuccessActions::render($reservation_data) ?>
                    </div>
                    <?php
                    // <<<< end of HTML response
                }
            } else {
                // Something went wrong, output a message
                echo Components\NavigationHeader::InPaymentForm();
                ?>
                <div class="tbk-slide-body">
                    <div class="ui negative message">
                        <div class="tbk-header">
                            <?= esc_html($reservation_data->getMessage()) ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo Toolkit\wrapAjaxResponse(ob_get_clean());
        } else {
            /*
             * $response is an error object
             */
            $template = new Frontend\ErrorMessages();
            $code = $response->getCode();
            ob_start();
            switch ($code) {
                case 1:
                    echo Toolkit\wrapAjaxResponse($template->coworkersRevokedAuth());
                    break;
                case 2:
                    echo Toolkit\wrapAjaxResponse($template->eventNotAvailableAnymore());
                    break;
                case 3:
                    echo Toolkit\wrapAjaxResponse($template->alreadyBooked());
                    break;
                case 4:
                    echo Toolkit\wrapAjaxResponse($template->invalidAttendeeEmail());
                    break;
                case 5:
                    echo Toolkit\wrapAjaxResponse($template->genericGoogleApiError($response->getMessage()));
                    break;
                case 6:
                    echo Toolkit\wrapAjaxResponse($template->eventFull());
                    break;
                case 8:
                    echo Toolkit\wrapAjaxResponse($template->customerMaxCumulativeTicketsOvercome());
                    break;
            }
            echo Toolkit\wrapAjaxResponse(ob_get_clean());
        }

        exit(); // bye bye
    }

    /**
     * Process the very first payment action
     *
     * @param null $reservation_checksum
     * @param null $gateway_id
     * @param null $reservation_database_id
     * @param bool $return
     *
     * @return bool|string|\TeamBooking_Error
     */
    public static function submitPayment($reservation_checksum = NULL, $gateway_id = NULL, $reservation_database_id = NULL, $return = FALSE)
    {
        ///////////////////////
        // Reservation data  //
        ///////////////////////
        if (NULL === $reservation_database_id) {
            // this function is called via Ajax, parameters are POSTed
            $reservation_database_id = filter_input(INPUT_POST, 'reservation_database_id');
        }
        if (NULL === $reservation_checksum) {
            // this function is called via Ajax, parameters are POSTed
            $reservation_checksum = filter_input(INPUT_POST, 'reservation_checksum');
        }
        $reservation_data = Database\Reservations::getById($reservation_database_id);
        if ($reservation_data->getToken() != $reservation_checksum) {
            // TODO
            exit;
        }
        ///////////////////
        // Gateway boot  //
        ///////////////////
        if (NULL === $gateway_id) {
            // this function is called via Ajax, parameters are POSTed
            $gateway_id = filter_input(INPUT_POST, 'gateway_id');
        }
        if (Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->isOffsite()) {
            // if the gateway is offsite, we call it directly
            $response = Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->prepareGateway($reservation_data);
        } else {
            // otherwise, we call specific data collecting form
            $response = Functions\getSettings()->getPaymentGatewaySettingObject($gateway_id)->getDataForm($reservation_data->getToken(), $reservation_database_id);
        }
        if ($return) return $response;
        echo Toolkit\wrapAjaxResponse($response);
        // bye bye
        exit;
    }

    //------------------------------------------------------------

    /**
     * Ajax callback: prepare the reservation form for confirmation
     */
    public static function prepareReservation()
    {
        // Map the reservation data
        $reservation_data = Mappers\reservationFormMapper($_POST['data']);

        // Is nonce verification failed?
        if (!$reservation_data) {
            $message = ProcessReservation::getErrorMessage(__('Please refresh the page...', 'team-booking'));
            exit(Toolkit\wrapAjaxResponse($message));
        }

        // Set the customer's timezone
        if ($_POST['timezone'] !== 'false') {
            $reservation_data->setCustomerTimezone(filter_var($_POST['timezone'], FILTER_SANITIZE_STRING));
        }

        // Checks for files
        // TODO: remove files if the reservation is not confirmed by customer
        if (!empty($_FILES)) {
            foreach ($_FILES as $hook => $file) {
                // TODO: server-side data validation
                $returned_handle = Functions\handleFileUpload($file);
                $reservation_data->addFileReference($hook, $returned_handle);
            }
        }

        // Set the coworker, if needed
        if ($reservation_data->getServiceClass() === 'unscheduled' && !$reservation_data->getCoworker()) {
            $reservation_data->setCoworker(\TeamBooking_Reservation::chooseCoworker($reservation_data->getServiceId()));
        }

        add_action('tbk_reservation_review_header', array(
            'TeamBooking\\Frontend\\Review',
            'review_header',
        ));

        ob_start();
        Functions\reservation_review_header($reservation_data->getServiceName());
        // WordPress custom action call
        Functions\reservation_before_processing($reservation_data);
        // Check if we should stop the reservation process
        if (!isset($reservation_data->stop) || !$reservation_data->stop) {
            // Reservation review step
            echo Review::get($reservation_data);
        }
        if (isset($reservation_data->skip_review) && $reservation_data->skip_review) {
            ob_end_clean();
            self::submitReservation($reservation_data);
        } else {
            echo Toolkit\wrapAjaxResponse(ob_get_clean());
        }
        exit;
    }

    /**
     * @param null|\TeamBooking_ReservationData $reservation_data
     * @param bool                              $to_frontend
     * @param string                            $who
     *
     * @return mixed
     */
    public static function submitReservation($reservation_data = NULL, $to_frontend = TRUE, $who = NULL)
    {
        if (NULL === $reservation_data) {
            $reservation_data = Toolkit\objDecode(filter_var($_POST['reservation']));
        }
        if (!($reservation_data instanceof \TeamBooking_ReservationData)) {
            if (!$to_frontend) return new \TeamBooking_Error(50);
            exit(Toolkit\wrapAjaxResponse(ProcessReservation::getErrorMessage(__('Reservation data cannot be read, please retry!', 'team-booking'))));
        }

        // Service existence check
        try {
            $service = Database\Services::get($reservation_data->getServiceId());
        } catch (\Exception $e) {
            if (!$to_frontend) return new \TeamBooking_Error(51);
            exit(Toolkit\wrapAjaxResponse($e->getMessage()));
        }

        /*
         * Let's save the reservation data.
         * 
         * If payment is requested immediately AND price is > 0
         * then we must set the reservation in PENDING status,
         * awaiting for the payment to be done
         */
        if ($service->getSettingsFor('payment') === 'immediately'
            && $reservation_data->getPriceIncremented() > 0
            && !current_user_can('manage_options') //Admins must skip the payment process
        ) {
            // pending
            $reservation_data->setStatusPending();
            /*
             * If it's already placed because the customer went back to change something,
             * just update the reservation record with the new reservation data
             */
            $already_placed = Database\Reservations::getByToken($reservation_data->getToken());
            if ($already_placed) {
                if (!$already_placed->isPending()) {
                    // it's there, but it's not pending, throw an advice
                    if (!$to_frontend) return new \TeamBooking_Error(52);
                    exit(Toolkit\wrapAjaxResponse(ProcessReservation::getErrorMessage(__('Reservation already confirmed!', 'team-booking'))));
                }
                $reservation_id = $already_placed->getDatabaseId();
                $reservation_data->setDatabaseId($reservation_id);
                Database\Reservations::update($reservation_data);
            } else {
                $reservation_id = Database\Reservations::insert($reservation_data);
                $reservation_data->setDatabaseId($reservation_id);
            }

            if (!$to_frontend) return array('response' => 1, 'state' => 'awaiting_payment', 'reservation_id' => $reservation_id);

            $active_payment_gateways = Functions\getSettings()->getPaymentGatewaysActive();

            /**
             * We have only one payment gateway active, and the payment is
             * requested immediately? Then, instead of showing payment choices,
             * we would rather skip the step and go directly into the payment
             * process
             */
            if (count($active_payment_gateways) === 1) {
                /* @var $gateway \TeamBooking_PaymentGateways_Settings */
                $gateway = reset($active_payment_gateways);
                $gateway_id = $gateway->getGatewayId();
                ProcessReservation::submitPayment($reservation_data->getToken(), $gateway_id, $reservation_id);
            } else {
                echo Toolkit\wrapAjaxResponse(Components\PaymentChoices::get($reservation_data));
            }
            exit;

        } else {
            // not pending
            if ($service->getSettingsFor('approval_rule') === 'none') {
                // put in confirmed status
                $reservation_data->setStatusConfirmed();
                // Push the reservation into the database
                $reservation_database_id = Database\Reservations::insert($reservation_data);
                $reservation_data->setDatabaseId($reservation_database_id);
                // confirm
                $reservation = new \TeamBooking_Reservation($reservation_data);
                $response = $reservation->doReservation($who);
                // Check for errors
                if ($response instanceof \TeamBooking_Error) {
                    // Remove the reservation record
                    Database\Reservations::delete($reservation_database_id);

                    if (!$to_frontend) return $response;

                    $template = new Frontend\ErrorMessages();
                    $code = $response->getCode();
                    switch ($code) {
                        case 1:
                            echo Toolkit\wrapAjaxResponse($template->coworkersRevokedAuth());
                            break;
                        case 2:
                            echo Toolkit\wrapAjaxResponse($template->eventNotAvailableAnymore());
                            break;
                        case 3:
                            echo Toolkit\wrapAjaxResponse($template->alreadyBooked());
                            break;
                        case 4:
                            echo Toolkit\wrapAjaxResponse($template->invalidAttendeeEmail());
                            break;
                        case 5:
                            echo Toolkit\wrapAjaxResponse($template->genericGoogleApiError($response->getMessage()));
                            break;
                        case 6:
                            echo Toolkit\wrapAjaxResponse($template->eventFull());
                            break;
                        case 8:
                            echo Toolkit\wrapAjaxResponse($template->customerMaxCumulativeTicketsOvercome());
                            break;
                    }
                    // bye bye
                    exit;
                }
            } else {
                // put in waiting status
                $reservation_data->setStatusWaitingApproval();

                // Push the reservation into the database
                $reservation_database_id = Database\Reservations::insert($reservation_data);
                $reservation_data->setDatabaseId($reservation_database_id);
            }

            // Send the notification e-mail
            if ($service->getEmailToAdmin('send')) {
                $reservation = new \TeamBooking_Reservation($reservation_data);
                $reservation->sendNotificationEmail();
            }

            if ($reservation_data->isWaitingApproval()) {

                // send notification e-mails
                if ($service->getSettingsFor('approval_rule') === 'coworker'
                    && Functions\getSettings()->getCoworkerData($reservation_data->getCoworker())->getCustomEventSettings($reservation_data->getServiceId())->getGetDetailsByEmail()
                ) {
                    $reservation = new \TeamBooking_Reservation($reservation_data);
                    $reservation->sendNotificationEmailToCoworker();
                }

                // let's check if we should redirect or not
                if ($service->getSettingsFor('redirect') && $service->getRedirectUrl()) {
                    if (!$to_frontend) return array(
                        'response'       => 1,
                        'state'          => 'awaiting_approval',
                        'reservation_id' => $reservation_database_id,
                        'redirect'       => $service->getRedirectUrl($reservation_database_id)
                    );
                    echo Toolkit\wrapAjaxResponse($service->getRedirectUrl($reservation_database_id));
                } else {
                    if (!$to_frontend) return array(
                        'response'       => 1,
                        'state'          => 'awaiting_approval',
                        'reservation_id' => $reservation_database_id
                    );
                    // start of HTML response >>>>
                    ob_start();
                    ?>
                    <?= Components\NavigationHeader::InReservationSuccess() ?>
                    <div class="tbk-slide-body">
                        <div class="tbk-positive-message-form">
                            <div class="tbk-message-header">
                                <?= Functions\modify_thankyou_content(esc_html__('Thank you for your reservation!', 'team-booking'), $reservation_data) ?>
                            </div>
                            <?php if ($service->getEmailToCustomer('send')) { ?>
                                <p><?= esc_html__("We'll send you an email when the reservation gets approved!", 'team-booking') ?></p>
                            <?php } ?>
                        </div>
                        <?php Components\SuccessActions::render($reservation_data) ?>
                    </div>
                    <?php
                    // <<<< end of HTML response
                    echo Toolkit\wrapAjaxResponse(ob_get_clean());
                }

            } else {

                // let's retrieve the updated reservation data
                $new_reservation_data = $response;
                $new_reservation_data->setDatabaseId($reservation_database_id);

                if ($service->getPrice() > 0 && $new_reservation_data->getPriceIncremented() <= 0) {
                    $new_reservation_data->setPaid(TRUE);
                }

                // Update reservation
                Database\Reservations::update($new_reservation_data);

                // send notification e-mails
                $reservation = new \TeamBooking_Reservation($new_reservation_data);
                if (Functions\getSettings()->getCoworkerData($new_reservation_data->getCoworker())->getCustomEventSettings($new_reservation_data->getServiceId())->getGetDetailsByEmail()) {
                    $reservation->sendNotificationEmailToCoworker();
                }
                if ($service->getEmailToCustomer('send')) {
                    $reservation->sendConfirmationEmail();
                }

                // let's check if we should redirect or not
                if ($service->getSettingsFor('redirect')) {
                    if (!$to_frontend) return array(
                        'response'       => 1,
                        'state'          => 'confirmed',
                        'reservation_id' => $reservation_database_id,
                        'redirect'       => $service->getRedirectUrl($reservation_database_id)
                    );
                    echo Toolkit\wrapAjaxResponse($service->getRedirectUrl($reservation_database_id));
                } else {
                    if (!$to_frontend) return array(
                        'response'       => 1,
                        'state'          => 'confirmed',
                        'reservation_id' => $reservation_database_id
                    );
                    // start of HTML response >>>>
                    ob_start();
                    ?>
                    <?= Components\NavigationHeader::InReservationSuccess() ?>
                    <div class="tbk-slide-body">
                        <div class="tbk-positive-message-form">
                            <div class="tbk-message-header">
                                <?= Functions\modify_thankyou_content(esc_html__('Thank you for your reservation!', 'team-booking'), $new_reservation_data) ?>
                            </div>
                            <?php if ($service->getEmailToCustomer('send')) { ?>
                                <p><?= esc_html__('We have sent you an email with details.', 'team-booking') ?></p>
                            <?php } ?>
                        </div>
                        <?php
                        if (!$new_reservation_data->isPaid()
                            && $new_reservation_data->getPriceIncremented() > 0
                            && $service->getSettingsFor('payment') !== 'later'
                            && Functions\getSettings()->thereIsAtLeastOneActivePaymentGateway()
                            && !current_user_can('manage_options')
                        ) {
                            ?>
                            <div class="ui centered tbk-header">
                                <?= esc_html__('Do you want to pay right now?', 'team-booking') ?>
                            </div>
                            <?php
                            Components\PaymentChoices::render($new_reservation_data, FALSE);
                        }
                        ?>
                        <br>
                        <?php Components\SuccessActions::render($new_reservation_data) ?>
                    </div>
                    <?php
                    // <<<< end of HTML response
                    echo Toolkit\wrapAjaxResponse(ob_get_clean());
                }
            }
            // bye bye
            exit;
        }
    }

    /**
     * @param $message
     *
     * @return string
     */
    public static function getErrorMessage($message)
    {
        return '<div class="tbk-slide-body"><div class="tbk-error-message-form" style="display: block"><div class="tbk-message-header">Oops!</div><p>' . esc_html($message) . '</p></div></div>';
    }

    public static function getIcalFile()
    {
        Files\generateICSFile(
            Toolkit\filterInput($_POST['summary'], TRUE) . '.ics',
            $_POST['start'],
            $_POST['end'],
            $_POST['address'],
            $_POST['description'],
            $_POST['uri'],
            $_POST['summary']
        );
        exit;
    }

}
