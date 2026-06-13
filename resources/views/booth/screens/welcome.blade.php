{{--
    Welcome Screen - Retro Pixel Art Theme
    =======================================
    Renders the welcome screen with retro 8-bit aesthetic.
    Variables:
      - $welcomeComponents: Collection of WelcomeScreenComponent
      - $project: Project model
--}}

@php
$backgroundComponent = $welcomeComponents->firstWhere('type', 'background');
$hasBackground = $backgroundComponent && isset($backgroundComponent->content['path']);
$fallbackBgColor = $project->welcome_background_color ?? '#87CEEB';
$screenStyle = '';
if (!$hasBackground && $fallbackBgColor) {
$screenStyle = 'background-color:' . e($fallbackBgColor) . ';';
}
@endphp

<div
    id="screen-welcome"
    class="booth-screen welcome-screen retro-pixel-theme @if(!$hasBackground && !$fallbackBgColor) welcome-screen--no-bg @endif"
    data-state="IDLE"
    @if ($screenStyle) style="<?php echo e($screenStyle); ?>" @endif>
    {{-- Background Layer --}}
    @if ($hasBackground)
    @php
    $bgPath = $backgroundComponent->content['path'];
    $bgUrl = (strpos($bgPath, 'http') === 0)
    ? $bgPath
    : Storage::disk('public')->url($bgPath);
    @endphp
    <img
        src="{{ $bgUrl }}"
        alt="Background"
        class="welcome-background" />
    @else
    <!-- Retro pixel art background elements -->
    <div class="retro-background">
        <div class="clouds"></div>
        <div class="grass"></div>
    </div>
    @endif

    {{-- Action Buttons (Top Right) --}}
    <div class="welcome-actions">
        <button
            type="button"
            id="btn-lock-fullscreen"
            class="booth-icon-btn"
            title="Toggle Fullscreen"
            aria-label="Toggle fullscreen">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
        </button>
        <button
            type="button"
            id="btn-camera-settings"
            class="booth-icon-btn"
            title="Camera Settings"
            aria-label="Camera settings">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.573-1.066z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>
    </div>

    {{-- Content Layer --}}
    <div class="welcome-content welcome-content--retro">
        {{-- Brand Text --}}
        <div class="retro-brand-text">YOUR BRAND</div>

        {{-- Main Title --}}
        <div class="retro-title">MY PROJECT</div>

        {{-- Start Button --}}
        <div class="welcome-component welcome-component--retro-button">
            <button
                type="button"
                id="btn-start"
                class="welcome-start-btn retro-start-btn">
                START
            </button>
        </div>

        {{-- Render Additional Components if provided --}}
        @foreach ($welcomeComponents->where('type', '!=', 'background')->where('type', '!=', 'button') as $component)
        @php
        $rawX = $component->content['x'] ?? 960;
        $rawY = $component->content['y'] ?? 540;
        $cx = (int) $rawX;
        $cy = (int) $rawY;
        $leftPct = $cx <= 100 ? $cx : ($cx / 1920) * 100;
            $topPct=$cy <=100 ? $cy : ($cy / 1080) * 100;
            $cw=$component->content['layoutWidth'] ?? $component->content['width'] ?? 'auto';
            $ch = $component->content['layoutHeight'] ?? $component->content['height'] ?? 'auto';
            $br = (int) ($component->content['borderRadius'] ?? 12);
            $fs = $component->content['fontSize'] ?? 'medium';
            $posStyle = 'left:' . $leftPct . '%;top:' . $topPct . '%;transform:translate(-50%,-50%);';
            $posStyle .= 'width:' . (is_numeric($cw) ? $cw . 'px' : $cw) . ';';
            $posStyle .= 'height:' . (is_numeric($ch) ? $ch . 'px' : $ch) . ';';
            $posStyle .= 'border-radius:' . $br . 'px;';
            $fontSize = match ($fs) {
            'small' => '1.25rem',
            'large' => '3rem',
            default => '2rem',
            };
            $alignment = $component->content['alignment'] ?? 'center';
            $textColorVal = $component->content['textColor'] ?? '#ffffff';
            $textStyle = 'font-size:' . $fontSize . ';color:' . e($textColorVal) . ';';
            @endphp
            @if ($component->type === 'text')
            <div class="welcome-component" style="<?php echo e($posStyle); ?>">
                <p class="welcome-text welcome-text--{{ $alignment }}" style="<?php echo e($textStyle); ?>">
                    {{ $component->content['text'] ?? '' }}
                </p>
            </div>
            @elseif ($component->type === 'image' && isset($component->content['path']))
            @php
            $imgWidthPx = ($component->content['width'] ?? 'auto') === 'custom'
            ? (int) ($component->content['customWidth'] ?? 200)
            : null;
            $imgPath = $component->content['path'];
            $imgUrl = (strpos($imgPath, 'http') === 0)
            ? $imgPath
            : Storage::disk('public')->url($imgPath);
            @endphp
            <div class="welcome-component" style="<?php echo e($posStyle); ?>">
                <img
                    src="{{ $imgUrl }}"
                    alt="Welcome Image"
                    class="welcome-image"
                    @if ($imgWidthPx) width="{{ $imgWidthPx }}" @endif />
            </div>
            @endif
            @endforeach
    </div>
</div>

<style>
    .retro-pixel-theme {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        font-family: 'Press Start 2P', 'Courier New', monospace;
        image-rendering: pixelated;
        image-rendering: -moz-crisp-edges;
        image-rendering: crisp-edges;
    }

    .welcome-content--retro {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .retro-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, #87CEEB 0%, #E0F6FF 50%, #90EE90 50%, #228B22 100%);
    }

    .retro-brand-text {
        position: absolute;
        top: 40px;
        font-size: 24px;
        color: #ffffff;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        letter-spacing: 4px;
        z-index: 5;
    }

    .retro-title {
        font-size: 120px;
        font-weight: bold;
        color: #FF0000;
        text-shadow:
            4px 4px 0px #8B0000,
            8px 8px 0px rgba(0, 0, 0, 0.3);
        margin: 0;
        line-height: 1;
        letter-spacing: 8px;
        animation: retroPulse 2s infinite;
    }

    @keyframes retroPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }
    }

    .welcome-component--retro-button {
        position: absolute;
        bottom: 240px;
        z-index: 5;
    }

    .retro-start-btn {
        padding: 20px 60px;
        font-size: 32px;
        font-weight: bold;
        background-color: #FF0000;
        color: #FFFFFF;
        border: 4px solid #8B0000;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Press Start 2P', 'Courier New', monospace;
        box-shadow:
            4px 4px 0px #8B0000,
            8px 8px 0px rgba(0, 0, 0, 0.3);
        transition: all 0.1s;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .retro-start-btn:hover {
        transform: translate(2px, 2px);
        box-shadow:
            2px 2px 0px #8B0000,
            4px 4px 0px rgba(0, 0, 0, 0.2);
    }

    .retro-start-btn:active {
        transform: translate(4px, 4px);
        box-shadow: 0px 0px 0px #8B0000;
    }

    .animate-bounce {
        animation: retroBounce 1s infinite;
    }

    @keyframes retroBounce {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }
    }

    /* Pixel art clouds and grass */
    .clouds {
        position: absolute;
        top: 80px;
        width: 100%;
        height: 200px;
        background:
            radial-gradient(circle at 20% 40%, rgba(255, 255, 255, 0.8) 8px, transparent 8px),
            radial-gradient(circle at 35% 50%, rgba(255, 255, 255, 0.9) 12px, transparent 12px),
            radial-gradient(circle at 50% 35%, rgba(255, 255, 255, 0.85) 10px, transparent 10px);
        background-size: 300px 150px;
        background-position: 0 0, 150px 0, 75px 0;
        background-repeat: repeat-x;
    }

    .grass {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 150px;
        background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    @media (max-width: 1024px) {
        .retro-title {
            font-size: 80px;
            letter-spacing: 4px;
        }

        .retro-brand-text {
            font-size: 18px;
            letter-spacing: 2px;
        }

        .retro-start-btn {
            padding: 16px 40px;
            font-size: 24px;
            letter-spacing: 1px;
        }
    }

    @media (max-width: 640px) {
        .retro-title {
            font-size: 48px;
            letter-spacing: 2px;
        }

        .retro-brand-text {
            font-size: 14px;
            letter-spacing: 1px;
        }

        .retro-start-btn {
            padding: 12px 28px;
            font-size: 16px;
            letter-spacing: 0.5px;
        }
    }
</style>