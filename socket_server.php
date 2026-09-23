<?php
/*
    PHP COMPONENT THAT MANAGES THE SOCKET SERVER.
    ITS MAIN FUNCTION IS TO KEEP THE SERVER CONSTANTLY 
    LISTENING FOR SOCKET CONNECTIONS.
    THE USER CAN START OR STOP THE SERVER.
*/

// Prevent direct URL access
if (!class_exists('PluginBridge')) {
	header("HTTP/1.1 403 Forbidden");
	die("Direct access forbidden.");
}

// Note: config.php is already loaded securely by the index.php router
include("sip_config.php");

// Action requested by the user
$action = $_POST['action'] ?? '';

if ($action === "start_server" || $action === "start_antenas") {

	if (session_status() === PHP_SESSION_ACTIVE) {
		session_write_close();
	}
	
	// Call to start the socket server
	error_reporting(~E_NOTICE);
	set_time_limit(0);
	$sock = create_socket();

	// A specific port is used for SC and antennas
	if ($action === "start_server") {
		$sock = bind_socket($sock, $ip_sip_server, $port_sip_server);
	} elseif ($action === "start_antenas") {
		$sock = bind_socket($sock, $ip_sip_server, $port_sip_antena);
	}

	$sock = listen_socket($sock, $max_clients);
	$master_socket = $sock;
	handle_inputs($sock, $max_clients, $master_socket, $enable_sip_log, $checksum_CRC, $maxretry);
} elseif ($action === "stop_server" || $action === "stop_antenas") {
	// Call to stop the socket server
	$semaphore = 'closeserver';
	if ($action === "stop_server") {
		stop_server($semaphore, $ip_sip_server, $port_sip_server);
	} elseif ($action === "stop_antenas") {
		stop_server($semaphore, $ip_sip_server, $port_sip_antena);
	}
}

function stop_server($semaphore, $ip_server, $port_server)
{
	// Creates a socket and sends a stop message identified on the server side
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	socket_connect($socket, $ip_server, $port_server);
	socket_write($socket, "closeserver", strlen("closeserver"));
	socket_close($socket);
	// Redirects back to the plugin interface
	header("Location: index.php?action=form");
}

function write_log($message)
{
	if ($file = fopen("log_sip.txt", "a")) {
		fwrite($file, date("d m Y H:i:s") . "|" . $message . "|" . PHP_EOL);
		fclose($file);
	}
}

function create_socket()
{
	// Function to create a socket
	if (!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		$message = "Error: Could not create socket (" . $errorcode . ": " . $errormsg . ")";
		write_log($message);
		die();
	} else {
		// ALLOW REUSE OF THE PORT IMMEDIATELY TO PREVENT ERROR 10048
		socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

		$message = "Socket created";
		write_log($message);
		return $sock;
	}
}

function bind_socket($sock, $ip_sip_server, $port_sip_server)
{
	// Function to bind a socket
	if (!socket_bind($sock, $ip_sip_server, $port_sip_server)) {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		$message = "Error: Could not bind socket " . $ip_sip_server . ":" . $port_sip_server . " (" . $errorcode . ": " . $errormsg . ")";
		write_log($message);
		die();
	} else {
		$message = "Socket bound to " . $ip_sip_server . ":" . $port_sip_server;
		write_log($message);
		return $sock;
	}
}

function listen_socket($sock, $max_clients)
{
	// Function to set the socket to listen
	if (!socket_listen($sock, $max_clients)) {
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);
		$message = "Error: Could not listen on socket (" . $errorcode . ": " . $errormsg . ")";
		write_log($message);
		die();
	} else {
		$message = "Listening on socket";
		write_log($message);
		return $sock;
	}
}

function handle_inputs($sock, $max_clients, $master_socket, $enable_sip_log, $checksum_CRC, $maxretry)
{
	$client_socks = array();
	$read = array();
	$array_AYR = array();

	// Start continuous reading to listen for incoming connections
	while (true) {
		$read = array();
		$read[0] = $sock;
		for ($i = 0; $i < $max_clients; $i++) {
			if (isset($client_socks[0][$i]) && $client_socks[0][$i] != null) {
				$read[$i + 1] = $client_socks[0][$i];
			}
		}
		// Declare the variables before passing them by reference
		$write = NULL;
		$except = NULL;
		$tv_sec = NULL;

		if (socket_select($read, $write, $except, $tv_sec) === false) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			die("Could not listen on socket: [$errorcode] $errormsg \n");
		}

		// If the master socket is in the read array, it's a new connection
		if (in_array($sock, $read)) {
			for ($i = 0; $i < $max_clients; $i++) {
				if (!isset($client_socks[0][$i]) || $client_socks[0][$i] == null) {
					$client_socks[0][$i] = socket_accept($sock);
					$client_socks[1][$i] = 1; // For AY control 1 to 9
					$client_socks[2][$i] = 0; // For max retries up to $maxretry

					if (socket_getpeername($client_socks[0][$i], $ip_client, $port_client)) {
						if ($enable_sip_log === "Y") {
							$message = "Client connected: $ip_client:$port_client";
							write_log($message);
						}
					}
					break;
				}
			}
		}

		// Check if any client socket sent data
		for ($i = 0; $i < $max_clients; $i++) {
			if (isset($client_socks[0][$i]) && in_array($client_socks[0][$i], $read)) {
				$input = socket_read($client_socks[0][$i], 1024);

				if ($input == null) {
					// Disconnected client
					if ($client_socks[0][$i] !== $master_socket) {
						socket_close($client_socks[0][$i]);
						unset($client_socks[0][$i]);
					}
				}

				if ($input === 'closeserver') {
					// Close master and all client sockets
					$message = "Server closed";
					write_log($message);
					socket_close($master_socket);
					unset($client_socks[0][0]);
					for ($y = 0; $y < $max_clients; $y++) {
						if (isset($client_socks[0][$y])) {
							socket_close($client_socks[0][$y]);
							unset($client_socks[0][$y]);
						}
					}
					break 2;
				}

				$request = $input;
				$tester = trim($request) . "&";

				if ($checksum_CRC === "Y") {
					if ($tester === "&") {
						$typeofresponse = "99d9";
						$response = "99d9";
					} elseif (substr($request, 0, 2) == "97") {
						$typeofresponse = "97";
						$response = "97";
					} else {
						// CRC Check
						$test = preg_split('/(.{4})$/', trim($request), 2, PREG_SPLIT_DELIM_CAPTURE);
						$sum = 0;
						$len = strlen($test[0]);
						for ($n = 0; $n < $len; $n++) {
							$sum += ord(substr($test[0], $n, 1));
						}
						$crc = ($sum & 0xFFFF) * -1;
						$resultado_crc = substr(sprintf("%4X", $crc), -4, 4);

						if ($resultado_crc === $test[1]) {
							$client_socks[2][$i] = 0;
							$typeofresponse = get_message($request);
						} else {
							if ($client_socks[2][$i] <= $maxretry) {
								$typeofresponse = "96";
								$response = "96";
								$client_socks[2][$i]++;
							} else {
								if ($client_socks[0][$i] !== $master_socket) {
									socket_close($client_socks[0][$i]);
									unset($client_socks[0][$i]);
								}
							}
						}
					}
				} else {
					$typeofresponse = get_message($request);
				}

				if (isset($response) && $response === "96" || (isset($typeofresponse) && $typeofresponse === "96")) {
					socket_write($client_socks[0][$i], $response);
				} elseif (isset($response) && $response === "99d9" || (isset($typeofresponse) && $typeofresponse === "99d9")) {
					// Empty byte control
				} elseif (isset($typeofresponse) && is_array($typeofresponse) && $typeofresponse[1] != 1 && $typeofresponse[0] != '') {
					$array_socket_AYR = array($client_socks[0][$i], $client_socks[1][$i]);

					if (isset($response) && $response === "97") {
						$response = $last_response;
					} else {
						$response = sip2($typeofresponse, $array_socket_AYR, $checksum_CRC);
					}
					socket_write($client_socks[0][$i], $response);
					$last_response = $response;
				}

				$response = null;
				$typeofresponse = null;
				$array_socket_ayr = null;
			}
		}
	}
}

function get_message($request_message)
{
	return (include 'case.php');
}

function sip2($array_request, $unsocket, $crc_enabled)
{
	return (include 'sip2.php');
}
