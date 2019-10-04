<div id="header">
    <div id="header-logo">
        <a href="/"><img src="/files/site_images/logo_line_tmp.png" /></a>  <!-- TODO SVG -->
    </div>

    <div id="navbar-toggle"><i class="material-icons">menu</i></div>

    <ul id="navbar">
        <a href="/models"><li>Models</li></a><!--
        --><a class='shrink-hack' href="https://hdrihaven.com/"><li>HDRIs</li></a><!--
        --><a class='shrink-hack' href="https://www.patreon.com/3dmodelhaven/posts?public=true"><li>News</li></a><!--
        --><a href="https://www.patreon.com/3dmodelhaven/overview"><li>Support Us</li></a><!--
        --><a href="/p/about-contact.php"><li>About/Contact</li></a>
    </ul>

    <div class='patreon-bar-wrapper' title="Next goal on Patreon: <?php
        echo goal_title($GLOBALS['PATREON_CURRENT_GOAL']);
        echo " ($";
        echo $GLOBALS['PATREON_EARNINGS'];
        echo " of $";
        echo $GLOBALS['PATREON_CURRENT_GOAL']['amount_cents']/100;
        echo ")";
        ?>">
        <a href="https://www.patreon.com/3dmodelhaven/overview">
        <div class="patreon-bar-outer">
            <div class="patreon-bar-inner-wrapper">
                <div class="patreon-bar-inner" style="width: <?php
                    echo $GLOBALS['PATREON_CURRENT_GOAL']['completed_percentage'] ?>%">
                    <div class='patreon-bar-text'>
                        <img src="/files/site_images/icons/patreon_logo.svg">
                        <span class="text">
                        <?php
                        echo "$";
                        echo ($GLOBALS['PATREON_CURRENT_GOAL']['amount_cents']/100) - $GLOBALS['PATREON_EARNINGS'];
                        echo " to go";
                        ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
</div>
<div class="nav-bar-spacer"></div>
<div id="push-footer">
