# SIP2 Manager (ABCD Plugin)

The SIP2 protocol implementation in the ABCD system manages connections for devices that assist in process automation, such as self-checkout equipment and remote readers. The module establishes multiple socket connections between the ABCD system (SIP server) and client devices using established protocols like TCP/IP. Information is exchanged through strings of characters called messages, initiating with a "request" from the client followed by a "response" from the server.

## 🚀 Architecture and Security
This module adheres to the modern modular architecture of the ABCD system[cite: 15]:
*   **Directory Structure:** The plugin is installed in the `/htdocs/content/plugins/` directory, keeping it independent and capable of supporting ABCD updates[cite: 14, 15].
*   **Security:** The `PluginBridge` class is used to provide secure access to essential paths and block direct unauthorized access[cite: 15].
*   **Routing:** It uses a basic MVC pattern where `index.php` acts as the main entry point (Router)[cite: 15].
*   **Internationalization:** Translations are handled via the `LanguageManager` class using `.tab` files inside the `/lang/` directory[cite: 15].

## 📡 Supported SIP2 Messages
The module listens for connections on port 5060 by default and supports the following messages:

| Pair | Message Name | Supported |
| :--- | :--- | :---: |
| 09-10 | Checkin | Yes |
| 11-12 | Checkout | Yes |
| 17-18 | Item Information | Yes |
| 19-20 | Item Status Update | Yes |
| 23-24 | Patron Status | Yes |
| 35-36 | End Session | Yes |
| 97-96 | Resend last message | Yes |
| 99-98 | SC-ACS Status | Yes |
| CRC | CRC control | Yes |
| AY | AY sequence number | Yes |

*Note: Messages for Block Patron, Hold, Patron Enable, Renew, Fee Paid, Patron Information, Renew All, and Login are currently not supported*.

## ⚙️ Configuration
Configuration parameters define how the server interacts with the network and databases:
*   **Network:** Parameters include the SIP server IP (`$ip_sip_server`), the listening port (`$port_sip_server`), and the maximum number of allowed connections (`$max_clients`).
*   **Timeouts and Retries:** Configurable values for message retries (`$maxretry`), transaction retries (`$retries_trasaction`), and timeouts (`$sip_timeout_sc`).
*   **Database Field Mapping:** Fields must be mapped for searching item titles (`$campo_titulo`), inventory identifiers (`$campo_inventario`), physical locations (`$campo_ubicacion`), and security types (`$campo_tipo_seguridad`).

## 🗄️ Database Requirements
For the protocol to function correctly, specific data lists and parameters must be present in the databases:
*   **Fines:** A table must be created in the `suspensions and fines` database (field 40) to define fee types recognized by the client (e.g., 04 for overdue, 03 for damage).
*   **User Blocks:** Field `v200` in the `suspml` database can be used to identify when a client automatically blocks a user.
*   **Security Markers:** An additional field should indicate the physical security type of the items, which is mapped in the `$campo_tipo_seguridad` parameter.

## 🛡️ Security Considerations
*   Connections to the SIP port may also allow external telnet connections. 
*   It is highly recommended to manage these connections using a server firewall or network security configurations.
*   Always specify the exact number of client machines that will connect using the `$max_clients` parameter so that no connections are left available for unauthorized access.