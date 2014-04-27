<?php
// security protection
prevent_direct_access_to_this_file();

function switcher($page)
{
    global $is_logged_in, $is_admin, $is_verified;

    try {
        $lock = false;

        if (!preg_match("/^[0-9_a-z]*$/", $page))
            $page = 'junk';

        // delay showing the header when logging in until we know whether the login worked or not
        if ($page != 'download' && $page != 'login' && $page != 'graph')
            show_header($page, $is_logged_in);

        if ($is_logged_in)
            get_user_lock($lock = $is_logged_in);

        addlog(LOG_SWITCHER, sprintf("[%s] visit page '%s'", getenv("REMOTE_ADDR"), $page));

        switch($page) {

            ////////////////////////////////////////////////////////////////////////
            // for general consumption
            ////////////////////////////////////////////////////////////////////////
            case '404':
            case 'graph':
            case 'help':
            case 'news':
            case 'orderbook':
            case 'test':
            case 'trade':
            case 'view_trades':
                include("$page.php");
                break;  

            ////////////////////////////////////////////////////////////////////////
            // for logged in users only
            ////////////////////////////////////////////////////////////////////////
            case 'api':
            case 'deposit':
            case 'identity':
            case 'place_order':
            case 'profile':
            case 'statement':
            case 'turn_on_duo':
            case 'view_order':
            case 'view_request':
            case 'withdraw':
                if ($is_logged_in)
                    include("$page.php");
                else
                    log_badpage($page);
                break;

            ////////////////////////////////////////////////////////////////////////
            // for admin only
            ////////////////////////////////////////////////////////////////////////
            case 'add_cash':
            case 'bank':
            case 'commission':
            case 'docs':
            case 'download':
            case 'freeze':
            case 'users':
                if ($is_admin)
                    include("$page.php");
                else
                    log_badpage($page);
                break;  

            case 'login':
                if (!$is_logged_in) {
                    include("login.php");

                    // we just tried to log in, so check whether or not it worked before showing the footer
                    get_login_status();
                } else {
                    addlog(LOG_LOGIN, "  already logged in");
                    log_badpage($page);
                }
                break;

            case 'logout':
                setcookie('autologin', FALSE, time() - 60*60*24*365);
                logout();

            default:
                sleep(3);
                log_badpage($page);
                break;
        }

        // debugging for session stuff
        if (0) {
            echo "<div class='content_box'>\n";
            echo "<h3>Debug</h3>\n";
            echo "<p>\n";
            echo "session id: ", session_id(), "<br/>\n";
            echo "session age: ", time() - $_SESSION['creation_time'], " seconds<br/>\n";
            if (isset($inactivity)) echo "you were inactive for $inactivity seconds<br/>\n";
            echo "MAX_IDLE_MINUTES_BEFORE_LOGOUT = ", MAX_IDLE_MINUTES_BEFORE_LOGOUT, " minutes = ", MAX_IDLE_MINUTES_BEFORE_LOGOUT * 60, " seconds<br/>\n";
            echo "MAX_SESSION_ID_LIFETIME_MINUTES = ", MAX_SESSION_ID_LIFETIME_MINUTES, " minutes = ", MAX_SESSION_ID_LIFETIME_MINUTES * 60, " seconds<br/>\n";
            echo "</p></div>\n";
        }
    }
    catch (Error $e) {
        global $shown_header;

        report_exception($e, SEVERITY::ERROR);
        // Same as below, but flag + log this for review,

        if (!$shown_header) show_header($page, $is_logged_in);
            
        echo "<div class='content_box'><h3>{$e->getTitle()}</h3>";
        echo "<p>{$e->getMessage()}</p></div>";
    }
    catch (Problem $e) {
        global $shown_header;
        if (!$shown_header) show_header($page, $is_logged_in);
            
        echo "<div class='content_box'><h3>{$e->getTitle()}</h3>";
        echo "<p>{$e->getMessage()}</p></div>";
    }
    catch (Exception $e) {
        global $shown_header;
        if (!$shown_header) show_header($page, $is_logged_in);
            
        echo "<div class='content_box'><h3>Technical difficulties</h3>";
        echo "<p>{$e->getMessage()}</p></div>";
    }

    show_footer($is_logged_in, $is_admin, $is_verified);

    if ($lock) release_lock($lock);
}
