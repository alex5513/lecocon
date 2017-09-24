<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'lecocon');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'durst');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'durst');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', 'localhost');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8mb4');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '.(&w6[m,`M{o`HW-G$DVFJ@`7I_K6H):RrWyZ?Z13|5Y.0|EQ^>Z7xfRU25<&+yN');
define('SECURE_AUTH_KEY',  '-?k^=]-E8/ ktl*2T$&XdhL}fAh0{](C<(x>IjJ)ue e,N(6YmmKq-%JuC@G5T|V');
define('LOGGED_IN_KEY',    '$N/,X;F^(tLUtkt@{/p!Kb#S^4u>rbYsFz)BFEz=M^0%ZB[=;AzE;AIT~$g3yyC4');
define('NONCE_KEY',        'HlvPPDxi ;Nfd>:#.nKAY4x>mjRw;J$?-Lv7sFPfBKd!*YT=8{590(_hR%4FU `z');
define('AUTH_SALT',        'wgf/v6[=Wx3Nj26+vZ%0.!0isHiU0wVAYD!osV4]r@&fH@iv{/:6WX)_A[$?r4Ri');
define('SECURE_AUTH_SALT', 'KI3T.~hBTLqbHg,1kfzDILt$E*hI;dlnV`G^FH)T,<tN.Zpb@(CGht,mge0-|%&_');
define('LOGGED_IN_SALT',   'N/QT1Vc#JYfY0(|,EUl>A8z0GAnTsI[6k#^%Tm<$TQcY[!PzZSW2(3:G0>8dp;lA');
define('NONCE_SALT',       'G<fcn@<CZ6i,]@2buDqRi;2k8IMK*$op-zDF`S,wF*Ws>N/5KKp0]2{kB_kBq0A$');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix  = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');