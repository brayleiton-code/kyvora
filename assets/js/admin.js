/**
 * Admin JS for WP Protect YourSite.
 */

( function( $ ) {
    'use strict';

    var wppys = window.wppysAdmin || {};

    $( document ).ready( function() {
        initModuleToggles();
        initLicenseActions();
        initWhitelist();
        initTools();
        initOnboarding();
    } );

    /**
     * Show WP admin notice.
     */
    function showNotice( message, type ) {
        type = type || 'success';
        var $notice = $( '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>' );
        $( '.wppys-wrap' ).prepend( $notice );
        // Auto-dismiss after 5 seconds
        setTimeout( function() {
            $notice.fadeOut( function() { $( this ).remove(); } );
        }, 5000 );
    }

    /**
     * Module toggles.
     */
    function initModuleToggles() {
        $( '#wppys-save-settings' ).on( 'click', function() {
            var $btn = $( this );
            var $form = $( '#wppys-modules-form' );
            var data = $form.serialize();

            $btn.prop( 'disabled', true ).text( wppys.strings.saving );
            $form.find( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, data + '&action=wppys_save_settings&nonce=' + wppys.nonce )
                .done( function( response ) {
                    if ( response.success ) {
                        $btn.text( wppys.strings.saved );
                        showNotice( wppys.strings.saved, 'success' );
                        setTimeout( function() {
                            $btn.prop( 'disabled', false ).text( 'Save Changes' );
                        }, 1500 );
                    } else {
                        showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                        $btn.prop( 'disabled', false ).text( 'Save Changes' );
                    }
                })
                .fail( function() {
                    showNotice( wppys.strings.error, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Save Changes' );
                })
                .always( function() {
                    $form.find( '.spinner' ).removeClass( 'is-active' );
                });
        } );
    }

    /**
     * License actions.
     */
    function initLicenseActions() {
        // Activate
        $( '#wppys-activate-license' ).on( 'click', function() {
            var $btn = $( this );
            var key = $( '#license_key' ).val().trim();

            if ( ! key ) {
                showNotice( 'License key is required.', 'error' );
                return;
            }

            $btn.prop( 'disabled', true ).text( wppys.strings.activating );
            $btn.siblings( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_activate_license',
                nonce: wppys.nonce,
                license_key: key
            })
            .done( function( response ) {
                if ( response.success ) {
                    showNotice( wppys.strings.activating + ' ' + response.data.message, 'success' );
                    setTimeout( function() { location.reload(); }, 1000 );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Activate' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $btn.prop( 'disabled', false ).text( 'Activate' );
            })
            .always( function() {
                $btn.siblings( '.spinner' ).removeClass( 'is-active' );
            });
        } );

        // Deactivate
        $( '#wppys-deactivate-license' ).on( 'click', function() {
            if ( ! confirm( wppys.strings.confirm ) ) {
                return;
            }

            var $btn = $( this );
            $btn.prop( 'disabled', true ).text( wppys.strings.deactivating );
            $btn.siblings( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_deactivate_license',
                nonce: wppys.nonce
            })
            .done( function( response ) {
                if ( response.success ) {
                    showNotice( wppys.strings.deactivating + ' ' + response.data.message, 'success' );
                    setTimeout( function() { location.reload(); }, 1000 );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Deactivate' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $btn.prop( 'disabled', false ).text( 'Deactivate' );
            })
            .always( function() {
                $btn.siblings( '.spinner' ).removeClass( 'is-active' );
            });
        } );

        // Verify
        $( '#wppys-verify-license, #wppys-reverify' ).on( 'click', function() {
            var $btn = $( this );
            $btn.prop( 'disabled', true ).text( wppys.strings.verifying );
            $btn.siblings( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_verify_license',
                nonce: wppys.nonce
            })
            .done( function( response ) {
                if ( response.success ) {
                    showNotice( wppys.strings.verifying + ' ' + response.data.message, 'success' );
                    setTimeout( function() { location.reload(); }, 1000 );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Verify Now' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $btn.prop( 'disabled', false ).text( 'Verify Now' );
            })
            .always( function() {
                $btn.siblings( '.spinner' ).removeClass( 'is-active' );
            });
        } );

        // Toggle key visibility (uses data attributes on <code> element)
        $( '#wppys-toggle-key' ).on( 'click', function() {
            var $code = $( '#wppys-license-key' );
            var $icon = $( this ).find( '.dashicons' );
            var isFull = $code.data( 'showing-full' );

            if ( ! isFull ) {
                // Show full key
                $code.text( $code.data( 'full-key' ) );
                $code.data( 'showing-full', true );
                $icon.removeClass( 'dashicons-visibility' ).addClass( 'dashicons-hidden' );
            } else {
                // Show obfuscated key
                $code.text( $code.data( 'obfuscated' ) );
                $code.data( 'showing-full', false );
                $icon.removeClass( 'dashicons-hidden' ).addClass( 'dashicons-visibility' );
            }
        } );
    }

    /**
     * Whitelist actions.
     */
    function initWhitelist() {
        // Add current IP
        $( '#wppys-add-current-ip' ).on( 'click', function() {
            var $textarea = $( '#whitelist_ips' );
            var currentIp = $( '#wppys-current-ip' ).text().trim();

            if ( currentIp && currentIp !== 'unknown' ) {
                var current = $textarea.val().trim();
                var ips = current ? current.split( '\n' ) : [];

                if ( ips.indexOf( currentIp ) === -1 ) {
                    ips.push( currentIp );
                    $textarea.val( ips.join( '\n' ) );
                }
            }
        } );

        // Save whitelist
        $( '#wppys-save-whitelist' ).on( 'click', function() {
            var $btn = $( this );
            var $form = $( '#wppys-whitelist-form' );
            var data = $form.serialize();

            $btn.prop( 'disabled', true ).text( wppys.strings.saving );
            $form.find( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, data + '&action=wppys_save_settings&nonce=' + wppys.nonce )
                .done( function( response ) {
                    if ( response.success ) {
                        $btn.text( wppys.strings.saved );
                        showNotice( wppys.strings.saved, 'success' );
                        setTimeout( function() {
                            $btn.prop( 'disabled', false ).text( 'Save Whitelist' );
                        }, 1500 );
                    } else {
                        showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                        $btn.prop( 'disabled', false ).text( 'Save Whitelist' );
                    }
                })
                .fail( function() {
                    showNotice( wppys.strings.error, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Save Whitelist' );
                })
                .always( function() {
                    $form.find( '.spinner' ).removeClass( 'is-active' );
                });
        } );
    }

    /**
     * Tools actions.
     */
    function initTools() {
        // Rollback - prepare
        $( '#wppys-rollback' ).on( 'click', function() {
            var $btn = $( this );
            $btn.prop( 'disabled', true ).text( wppys.strings.rollingBack );
            $btn.siblings( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_rollback',
                nonce: wppys.nonce
            })
            .done( function( response ) {
                if ( response.success ) {
                    // Store download info for install step
                    $( '#wppys-rollback-result' ).data( 'download-url', response.data.download_url );
                    $( '#wppys-rollback-result' ).data( 'sha256', response.data.sha256 );
                    $( '#wppys-rollback-link' ).text( 'Install ' + response.data.version );
                    $( '#wppys-rollback-result' ).show();
                    $btn.hide();
                    showNotice( 'Rollback prepared: ' + response.data.version, 'success' );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $btn.prop( 'disabled', false ).text( 'Prepare Rollback' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $btn.prop( 'disabled', false ).text( 'Prepare Rollback' );
            })
            .always( function() {
                $btn.siblings( '.spinner' ).removeClass( 'is-active' );
            });
        } );

        // Rollback - install
        $( '#wppys-rollback-link' ).on( 'click', function( e ) {
            e.preventDefault();
            var $link = $( this );
            var $resultDiv = $( '#wppys-rollback-result' );
            var downloadUrl = $resultDiv.data( 'download-url' );
            var sha256 = $resultDiv.data( 'sha256' );

            if ( ! downloadUrl ) {
                return;
            }

            if ( ! confirm( 'This will install the previous version. Make sure you have a backup. Continue?' ) ) {
                return;
            }

            $link.prop( 'disabled', true ).text( 'Installing...' );
            $link.siblings( '.spinner' ).addClass( 'is-active' );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_install_rollback',
                nonce: wppys.nonce,
                download_url: downloadUrl,
                sha256: sha256
            })
            .done( function( response ) {
                if ( response.success ) {
                    showNotice( response.data.message, 'success' );
                    $link.text( 'Installed' ).prop( 'disabled', true );
                    setTimeout( function() { location.reload(); }, 2000 );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $link.prop( 'disabled', false ).text( 'Retry Install' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $link.prop( 'disabled', false ).text( 'Retry Install' );
            })
            .always( function() {
                $link.siblings( '.spinner' ).removeClass( 'is-active' );
            });
        } );

        // Reset onboarding
        $( '#wppys-reset-onboarding' ).on( 'click', function() {
            if ( ! confirm( wppys.strings.confirm ) ) {
                return;
            }

            var $btn = $( this );
            $btn.prop( 'disabled', true );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_reset_onboarding',
                nonce: wppys.nonce
            })
            .done( function( response ) {
                if ( response.success ) {
                    showNotice( 'Onboarding reset', 'success' );
                    setTimeout( function() { location.reload(); }, 1000 );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                    $btn.prop( 'disabled', false );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
                $btn.prop( 'disabled', false );
            });
        } );

        // Export log
        $( '#wppys-export-log' ).on( 'click', function() {
            var $btn = $( this );
            $btn.prop( 'disabled', true );

            $.post( wppys.ajaxUrl, {
                action: 'wppys_export_log',
                nonce: wppys.nonce
            })
            .done( function( response ) {
                if ( response.success && response.data.url ) {
                    window.open( response.data.url, '_blank' );
                    showNotice( 'Log downloaded', 'success' );
                } else {
                    showNotice( wppys.strings.error + ': ' + response.data, 'error' );
                }
            })
            .fail( function() {
                showNotice( wppys.strings.error, 'error' );
            })
            .always( function() {
                $btn.prop( 'disabled', false );
            });
        } );
    }

    /**
     * Onboarding.
     */
    function initOnboarding() {
        // Level card selection
        $( '.wppys-level-card' ).on( 'click', function() {
            $( '.wppys-level-card' ).removeClass( 'selected' );
            $( this ).addClass( 'selected' ).find( 'input' ).prop( 'checked', true );
        } );

        // Auto-submit on level change (optional UX)
        $( 'input[name="level"]' ).on( 'change', function() {
            // Visual feedback only
        } );
    }

} )( jQuery );