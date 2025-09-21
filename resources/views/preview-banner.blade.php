<div id="theme-preview-banner" style="
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 99999;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.4;
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
">
    <div style="
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    ">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 12px;
                background: rgba(255,255,255,0.15);
                border-radius: 8px;
                border: 1px solid rgba(255,255,255,0.2);
            ">
                <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
                </svg>
                <span style="font-weight: 600; font-size: 13px; letter-spacing: 0.5px;">AlizHarb</span>
            </div>

            <!-- Theme Preview Info -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="
                    background: rgba(255,255,255,0.1);
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                ">PREVIEW</div>
                <div>
                    <div style="font-weight: 600;">{{ $name }}</div>
                    <div style="font-size: 12px; opacity: 0.8;">Theme preview active</div>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <form method="POST" action="{{ route('theme.preview.activate', $slug) }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="url" value="{{ request()->fullUrl() }}">
                <button type="submit" style="
                    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
                    display: flex;
                    align-items: center;
                    gap: 6px;
                " onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(34, 197, 94, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(34, 197, 94, 0.3)'">
                    <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Activate Theme
                </button>
            </form>

            <a href="{{ route('theme.preview.exit', ['url' => request()->fullUrl()]) }}" style="
                background: rgba(255,255,255,0.1);
                color: white;
                border: 1px solid rgba(255,255,255,0.2);
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 6px;
            " onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateY(-1px)'"
               onmouseout="this.style.background='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                Exit Preview
            </a>

            <button onclick="hideBanner()" style="
                background: none;
                border: none;
                color: white;
                font-size: 16px;
                cursor: pointer;
                padding: 8px;
                opacity: 0.6;
                transition: all 0.2s;
                border-radius: 4px;
            " onmouseover="this.style.opacity='1'; this.style.background='rgba(255,255,255,0.1)'"
               onmouseout="this.style.opacity='0.6'; this.style.background='none'"
               title="Hide banner (Ctrl+H to show again)">
                <svg style="width: 14px; height: 14px;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    // Banner functionality
    (function() {
        const banner = document.getElementById('theme-preview-banner');
        let isHidden = false;

        // Adjust body margin to account for banner
        function adjustBodyMargin() {
            if (!isHidden) {
                document.body.style.marginTop = '66px';
                document.body.style.transition = 'margin-top 0.3s ease';
            } else {
                document.body.style.marginTop = '0px';
            }
        }

        // Hide banner function
        window.hideBanner = function() {
            if (banner) {
                banner.style.transform = 'translateY(-100%)';
                banner.style.transition = 'transform 0.3s ease';
                isHidden = true;
                adjustBodyMargin();

                // Show a small indicator to restore banner
                showRestoreIndicator();
            }
        };

        // Show banner function
        window.showBanner = function() {
            if (banner) {
                banner.style.transform = 'translateY(0%)';
                isHidden = false;
                adjustBodyMargin();
                hideRestoreIndicator();
            }
        };

        // Show restore indicator
        function showRestoreIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'theme-preview-indicator';
            indicator.innerHTML = `
                <div style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    cursor: pointer;
                ">
                    <svg style="width: 16px; height: 16px;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7v10c0 5.55 3.84 9.74 9 11 5.16-1.26 9-5.45 9-11V7l-10-5z"/>
                    </svg>
                    <span style="font-size: 12px; font-weight: 600;">Theme Preview</span>
                    <svg style="width: 12px; height: 12px;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            `;
            indicator.style.cssText = `
                position: fixed;
                top: 10px;
                right: 20px;
                z-index: 99998;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 8px 12px;
                border-radius: 8px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid rgba(255,255,255,0.1);
            `;

            indicator.onclick = showBanner;
            indicator.onmouseover = function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 6px 25px rgba(0,0,0,0.2)';
            };
            indicator.onmouseout = function() {
                this.style.transform = 'translateY(0px)';
                this.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
            };

            document.body.appendChild(indicator);
        }

        // Hide restore indicator
        function hideRestoreIndicator() {
            const indicator = document.getElementById('theme-preview-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        // Keyboard shortcut: Ctrl+H to toggle banner
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'h') {
                e.preventDefault();
                if (isHidden) {
                    showBanner();
                } else {
                    hideBanner();
                }
            }
        });

        // Initialize
        adjustBodyMargin();

        // Subtle animation on load
        if (banner) {
            banner.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                banner.style.transition = 'transform 0.5s ease';
                banner.style.transform = 'translateY(0%)';
            }, 100);
        }

        // Auto-reduce opacity after 15 seconds (subtle reminder it can be hidden)
        setTimeout(() => {
            if (banner && !isHidden) {
                banner.style.opacity = '0.9';
                setTimeout(() => {
                    if (banner && !isHidden) {
                        banner.style.opacity = '1';
                    }
                }, 2000);
            }
        }, 15000);
    })();
</script>
