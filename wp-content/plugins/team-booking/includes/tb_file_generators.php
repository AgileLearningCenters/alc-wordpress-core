<?php

namespace TeamBooking\Files;
defined('ABSPATH') or die('No script kiddies please!');
use TeamBooking\Functions,
    TeamBooking\Database,
    TeamBooking\Toolkit;

/**
 * Generate ICS file
 *
 * @param $filename    - file name
 * @param $datestart   - starting date (in seconds since unix epoch)
 * @param $dateend     - ending date (in seconds since unix epoch)
 * @param $address     - event's address
 * @param $description - URL of the event (add http://)
 * @param $uri         - text description of the event
 * @param $summary     - text title of the event
 */
function generateICSFile($filename, $datestart, $dateend, $address, $description, $uri, $summary)
{
    // 1. Set the correct headers for this file
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    // 2. Echo out the ics file's contents
    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
    echo "CALSCALE:GREGORIAN\r\n";
    echo "BEGIN:VEVENT\r\n";
    echo 'DTEND:' . date('Ymd\THis\Z', $dateend) . "\r\n";
    echo 'UID:' . uniqid('', TRUE) . "\r\n";
    echo 'DTSTAMP:' . date('Ymd\THis\Z', current_time('timestamp')) . "\r\n";
    echo 'LOCATION:' . preg_replace('/([\,;])/', '\\\$1', $address) . "\r\n";
    echo 'DESCRIPTION:' . preg_replace('/([\,;])/', '\\\$1', $description) . "\r\n";
    echo 'URL;VALUE=URI:' . preg_replace('/([\,;])/', '\\\$1', $uri) . "\r\n";
    echo 'SUMMARY:' . preg_replace('/([\,;])/', '\\\$1', $summary) . "\r\n";
    echo 'DTSTART:' . date('Ymd\THis\Z', $datestart) . "\r\n";
    echo "END:VEVENT\r\n";
    echo "END:VCALENDAR\r\n";
}

function generateReservationPDF(\TeamBooking_ReservationData $reservation)
{
    ob_start();
    include_once TEAMBOOKING_PATH . '/includes/tb_pdf_reservation_model.php';

    return ob_get_clean();
}

/**
 * Generate an XLSX file with customers
 *
 * @param string $filename
 *
 * @return mixed
 * @throws \Exception
 */
function generateXLSXClients($filename = 'customers.xlsx')
{
    $reservations = Database\Reservations::getAll();
    $wp_users = get_users();
    $services = Database\Services::get();
    $customers = array();

    foreach ($reservations as $reservation) {
        if (!$reservation->getCustomerUserId()) {
            // Not registered, search email match
            $found = FALSE;
            foreach ($wp_users as $user) {
                if ($user->user_email == $reservation->getCustomerEmail()) {
                    $customers[ $user->ID ] = new \TeamBooking\Customer($user, $reservations);
                    $found = TRUE;
                }
            }
            if (!$found) {
                $user = new \WP_User();
                $user->display_name = $reservation->getCustomerDisplayName();
                $user->user_email = $reservation->getCustomerEmail();
                $customers[ $user->email ] = new \TeamBooking\Customer($user, $reservations);
            }
        } else {
            foreach ($wp_users as $user) {
                if ($user->ID == $reservation->getCustomerUserId()) {
                    $customers[ $user->ID ] = new \TeamBooking\Customer($user, $reservations);
                }
            }
        }
    }

    ob_start();
    header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new \XLSXWriter();
    $writer->setAuthor(get_bloginfo('name'));
    $rows = array();

    // Preparing the header array
    $headers = array(
        __('Status', 'team-booking')             => 'string',
        __('Name', 'team-booking')               => 'string',
        __('Email', 'team-booking')              => 'string',
        __('Total Reservations', 'team-booking') => 'string',
    );

    foreach ($services as $service) {
        $headers[ $service->getName() ] = 'string';
    }

    foreach ($customers as $customer) {
        /** @var $customer \TeamBooking\Customer */
        $row = array(
            $customer->getID() ? __('Registered', 'team-booking') : __('Guest', 'team-booking'),
            $customer->getName(),
            $customer->getEmail(),
            $customer->getTotalReservations(),
        );

        foreach ($services as $service) {
            $row[] = $customer->getReservations($service->getId());
        }

        $rows[] = $row;
    }
    $writer->writeSheet($rows, '', $headers);
    $writer->writeToStdOut();

    return ob_get_clean();
}

/**
 * Generate an XLSX file with given reservation records
 *
 * @param null|\TeamBooking_ReservationData[] $reservations
 * @param string                              $filename
 *
 * @return mixed
 */
function generateXLSXFile($reservations = NULL, $filename = 'reservations.xlsx')
{
    if (NULL === $reservations) {
        $reservations = Database\Reservations::getAll();
    }
    $timezone = Toolkit\getTimezone();
    ob_start();
    header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    $writer = new \XLSXWriter();
    $writer->setAuthor(get_bloginfo('name'));
    $headers = array();
    $rows = array();

    foreach ($reservations as $id => $reservation) {
        $time = $reservation->getCreationInstant(); // UTC, seconds
        if (NULL !== $reservation->getStart()) {
            $date_time_object = \DateTime::createFromFormat('U', $reservation->getStart());
            $date_time_object->setTimezone($timezone);
            $when_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
                . ' '
                . Functions\date_i18n_tb(get_option('time_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset());
        } else {
            $when_value = esc_html__('Unscheduled', 'team-booking');
        }
        $date_time_object = \DateTime::createFromFormat('U', $time);
        $date_time_object->setTimezone($timezone);
        $date_time_of_reservation_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
            . ' '
            . Functions\date_i18n_tb(get_option('time_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset());
        // This skips old logs before v.1.2 if present
        if (!$reservation instanceof \TeamBooking_ReservationData) {
            continue;
        }

        // If not admin, keep only logs relative to current coworker
        $coworker_id = get_current_user_id();
        if (!current_user_can('manage_options') && $reservation->getCoworker() != $coworker_id) {
            continue;
        }

        $headers[ $reservation->getServiceId() ] = array(
            __('ID', 'team-booking')                  => 'string',
            __('Service', 'team-booking')             => 'string',
            __('When', 'team-booking')                => 'string', //YYYY-MM-DD HH:MM:SS (Y-m-d h:i:s)
            __('Date of reservation', 'team-booking') => 'string',
            __('Reservation Status', 'team-booking')  => 'string',
            __('Payment Status', 'team-booking')      => 'string',
            __('Price', 'team-booking')               => 'money',
            __('Currency', 'team-booking')            => 'string',
        );
        if ($reservation->getServiceClass() === 'event') {
            $headers[ $reservation->getServiceId() ][ __('Tickets', 'team-booking') ] = 'string'; //4th column
            $headers[ $reservation->getServiceId() ][ __('Total price paid', 'team-booking') ] = 'money'; //5th column
        }
        if (current_user_can('manage_options')) {
            $headers[ $reservation->getServiceId() ][ __('Coworker', 'team-booking') ] = 'string'; //6th column
        }
        $headers[ $reservation->getServiceId() ][ __('Discounts used', 'team-booking') ] = 'string';
        $headers[ $reservation->getServiceId() ][ __('WordPress User', 'team-booking') ] = 'string';

        foreach ($reservation->getFieldsArray() as $key => $value) {
            $headers[ $reservation->getServiceId() ][ ucwords(str_replace('_', ' ', $key)) ] = 'string';
        }

        // Prepare the payment status
        if ($reservation->isPaid()) {
            $payment_status = __('paid', 'team-booking');
        } else {
            $payment_status = __('not paid', 'team-booking');
        }

        if ($reservation->getStatus() === 'confirmed' && $reservation->getServiceClass() === 'unscheduled') {
            $reservation_status = __('todo', 'team-booking');
        } else {
            $reservation_status = $reservation->getStatus();
        }

        $row = array(
            $reservation->getDatabaseId(),
            $reservation->getServiceName(),
            $when_value,
            $date_time_of_reservation_value,
            $reservation_status,
            $payment_status,
            $reservation->getPrice(),
            $reservation->getCurrencyCode(),
        );
        if ($reservation->getServiceClass() === 'event') {
            $row[] = $reservation->getTickets(); //4th column
            $row[] = $reservation->getPriceIncremented() * $reservation->getTickets(); //5th column
        }
        if (current_user_can('manage_options')) {
            $row[] = Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getEmail(); //6th column
        }

        $discount_names = array();
        foreach ($reservation->getDiscount() as $discount) {
            $discount_names[] = $discount['name'] . (isset($discount['coupon']) ? (' (' . $discount['coupon'] . ')') : '');
        }
        $row[] = implode(',', $discount_names);

        if ($reservation->getCustomerUserId()) {
            $user_data = get_userdata($reservation->getCustomerUserId());
            $row[] = $user_data ? $user_data->user_nicename : __('User removed', 'team-booking');
        } else {
            $row[] = '';
        }

        foreach ($reservation->getFieldsArray() as $key => $value) {
            $row[] = Toolkit\unfilterInput($value);
        }
        $rows[ $reservation->getServiceId() ][] = $row;
    }
    foreach ($headers as $service => $header) {
        $title = $rows[ $service ][0][0];
        $writer->writeSheet($rows[ $service ], $title, $header);
    }
    $writer->writeToStdOut();

    return ob_get_clean();
}

/**
 * @param string $filename
 *
 * @return mixed
 * @throws \Exception
 */
function generateCSVClients($filename = 'customers.csv')
{
    $reservations = Database\Reservations::getAll();
    $wp_users = get_users();
    $services = Database\Services::get();
    $customers = array();

    foreach ($reservations as $reservation) {
        if (!$reservation->getCustomerUserId()) {
            // Not registered, search email match
            $found = FALSE;
            foreach ($wp_users as $user) {
                if ($user->user_email == $reservation->getCustomerEmail()) {
                    $customers[ $user->ID ] = new \TeamBooking\Customer($user, $reservations);
                    $found = TRUE;
                }
            }
            if (!$found) {
                $user = new \WP_User();
                $user->display_name = $reservation->getCustomerDisplayName();
                $user->user_email = $reservation->getCustomerEmail();
                $customers[ $user->email ] = new \TeamBooking\Customer($user, $reservations);
            }
        } else {
            foreach ($wp_users as $user) {
                if ($user->ID == $reservation->getCustomerUserId()) {
                    $customers[ $user->ID ] = new \TeamBooking\Customer($user, $reservations);
                }
            }
        }
    }

    ob_start();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Preparing the header array
    $header_array = array(
        __('Status', 'team-booking'),
        __('Name', 'team-booking'),
        __('Email', 'team-booking'),
        __('Total Reservations', 'team-booking'),
    );

    foreach ($services as $service) {
        $header_array[] = $service->getName();
    }

    // Output header
    fputcsv($output, $header_array);

    foreach ($customers as $customer) {
        /** @var $customer \TeamBooking\Customer */
        $row = array(
            $customer->getID() ? __('Registered', 'team-booking') : __('Guest', 'team-booking'),
            $customer->getName(),
            $customer->getEmail(),
            $customer->getTotalReservations(),
        );

        foreach ($services as $service) {
            $row[] = $customer->getReservations($service->getId());
        }

        fputcsv($output, $row);
    }

    return ob_get_clean();
}

/**
 * Generate a CSV file with given reservation records
 *
 * @param null|\TeamBooking_ReservationData[] $reservations
 * @param string                              $filename
 *
 * @return mixed
 */
function generateCSVFile($reservations = NULL, $filename = 'reservations.csv')
{
    if (NULL === $reservations) {
        $reservations = Database\Reservations::getAll();
    }
    $timezone = Toolkit\getTimezone();
    ob_start();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');

    // Preparing the header array
    $header_array = array(
        __('ID', 'team-booking'),
        __('Service', 'team-booking'),
        __('When', 'team-booking'),
        __('Date of reservation', 'team-booking'),
        __('Reservation Status', 'team-booking'),
        __('Payment Status', 'team-booking'),
        __('Price', 'team-booking'),
        __('Tickets', 'team-booking'),
        __('Total price paid', 'team-booking'),
    );
    if (current_user_can('manage_options')) {
        $header_array[] = __('Coworker', 'team-booking'); //4th column
    }
    $header_array[] = __('Discounts used', 'team-booking');
    $header_array[] = __('WordPress User', 'team-booking');
    $header_array[] = __('Details', 'team-booking');
    // Output header
    fputcsv($output, $header_array);
    foreach ($reservations as $id => $reservation) {
        $time = $reservation->getCreationInstant(); // UTC, seconds
        if (NULL !== $reservation->getStart()) {
            $date_time_object = \DateTime::createFromFormat('U', $reservation->getStart());
            $date_time_object->setTimezone($timezone);
            $when_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
                . ' '
                . Functions\date_i18n_tb(get_option('time_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset());
        } else {
            $when_value = esc_html__('Unscheduled', 'team-booking');
        }
        $date_time_object = \DateTime::createFromFormat('U', $time);
        $date_time_object->setTimezone($timezone);
        $date_time_of_reservation_value = Functions\date_i18n_tb(get_option('date_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset())
            . ' '
            . Functions\date_i18n_tb(get_option('time_format'), $date_time_object->getTimestamp() + $date_time_object->getOffset());
        // This skips old logs before v.1.2 if present
        if (!$reservation instanceof \TeamBooking_ReservationData) {
            continue;
        }
        // If not admin, keep only logs relative to current coworker
        $coworker_id = get_current_user_id();
        if (!current_user_can('manage_options') && $reservation->getCoworker() != $coworker_id) {
            continue;
        }

        // Prepare the payment status
        if ($reservation->isPaid()) {
            $payment_status = __('paid', 'team-booking');
        } else {
            $payment_status = __('not paid', 'team-booking');
        }

        if ($reservation->getPrice() > 0) {
            $price_string = $reservation->getCurrencyCode() . ' ' . $reservation->getPrice();
            $total_price_string = $reservation->getCurrencyCode() . ' ' . $reservation->getPriceIncremented() * $reservation->getTickets();
        } else {
            $price_string = 0;
            $total_price_string = 0;
        }

        if ($reservation->getStatus() === 'confirmed' && $reservation->getServiceClass() === 'unscheduled') {
            $reservation_status = __('todo', 'team-booking');
        } else {
            $reservation_status = $reservation->getStatus();
        }

        $row = array(
            $reservation->getDatabaseId(),
            $reservation->getServiceName(),
            $when_value,
            $date_time_of_reservation_value,
            $reservation_status,
            $payment_status,
            $price_string,
            $reservation->getTickets(),
            $total_price_string,
        );
        if (current_user_can('manage_options')) {
            $row[] = Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getEmail(); //4th column
        }

        $discount_names = array();
        foreach ($reservation->getDiscount() as $discount) {
            $discount_names[] = $discount['name'] . (isset($discount['coupon']) ? (' (' . $discount['coupon'] . ')') : '');
        }
        $row[] = implode(',', $discount_names);

        if ($reservation->getCustomerUserId()) {
            $user_data = get_userdata($reservation->getCustomerUserId());
            $row[] = $user_data ? $user_data->user_nicename : __('User removed', 'team-booking');
        } else {
            $row[] = '';
        }

        $details = '';
        foreach ($reservation->getFieldsArray() as $key => $value) {
            $details .= ucwords(str_replace('_', ' ', $key)) . ': ' . Toolkit\unfilterInput($value) . '-';
        }
        $row[] = $details;
        fputcsv($output, $row);
    }

    return ob_get_clean();
}

/**
 * Generate the settings backup file
 */
function generateSettingsBackup()
{
    $settings = serialize(Functions\getSettings());
    ob_start();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename=team-booking-settings-backup.tbk');
    $output = fopen('php://output', 'w');
    fwrite($output, $settings);

    return ob_get_clean();
}
