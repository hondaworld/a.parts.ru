/*
 *  Document   : bootstrap.js
 *  Author     : pixelcave
 *  Description: Import global dependencies
 *
 */

/*
 ********************************************************************************************
 *
 * If you would like to use webpack to handle all required core JS files, you can uncomment
 * the following imports and window assignments to have them included in the compiled
 * oneui.app.min.js as well.
 *
 * After that change, you won't have to include oneui.core.min.js in your pages any more
 *
 *********************************************************************************************
 */

// Import all vital core JS files..
import jQuery from 'jquery';
import SimpleBar from 'simplebar';
import Cookies from 'js-cookie';
// import 'bootstrap';
import "../../node_modules/bootstrap/js/dist/util.js";
import "../../node_modules/bootstrap/js/dist/alert.js";
import "../../node_modules/bootstrap/js/dist/button.js";
// import "../../node_modules/bootstrap/js/dist/carousel.js";
import "../../node_modules/bootstrap/js/dist/collapse.js";
import "../../node_modules/bootstrap/js/dist/dropdown.js";
import "../../node_modules/bootstrap/js/dist/modal.js";
import "../../node_modules/bootstrap/js/dist/scrollspy.js";
import "../../node_modules/bootstrap/js/dist/tab.js";
import "../../node_modules/bootstrap/js/dist/tooltip.js";
import "../../node_modules/bootstrap/js/dist/popover.js";
import "../../node_modules/bootstrap/js/dist/toast.js";

import 'bootstrap-notify';
import 'popper.js';
import 'jquery.appear';
import 'jquery-ui-dist/jquery-ui';
import 'jquery-scroll-lock';
import 'select2/dist/js/select2.full'
import 'jquery.maskedinput/src/jquery.maskedinput'

// ..and assign to window the ones that need it
window.jQuery       = jQuery;
window.SimpleBar    = SimpleBar;
window.Cookies      = Cookies;
