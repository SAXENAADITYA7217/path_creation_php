<style>
    /* Center the iframe wrapper */
    .iframe-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 60px 0;
    }

    /* Outer glowing border box */
    .iframe-wrapper {
        position: relative;
        width: 90%;
        height: 800px;
        border-radius: 15px;
        padding: 5px;
        overflow: hidden;
    }

    /* Create an animated gradient border using ::before pseudo-element */
    .iframe-wrapper::before {
        content: "";
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 18px;
        background: linear-gradient(90deg,
            red, orange, yellow, green, cyan, blue, violet, red);
        background-size: 300%;
        z-index: 0;
        animation: moveCorners 4s linear infinite;
    }

    /* Animation that makes gradient move slightly around corners */
    @keyframes moveCorners {
        0%   { background-position: 0% 0%; }
        25%  { background-position: 50% 0%; }
        50%  { background-position: 100% 50%; }
        75%  { background-position: 50% 100%; }
        100% { background-position: 0% 0%; }
    }

    /* Inner iframe box */
    iframe {
        position: relative;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 10px;
        z-index: 1;
        display: block;
    }

    /* Optional: add soft glow */
    .iframe-wrapper {
        box-shadow: 0 0 25px rgba(255, 255, 255, 0.2),
                    0 0 50px rgba(255, 255, 255, 0.1);
    }
</style>

<div class="iframe-container">
    <div class="iframe-wrapper">
        <iframe
            src="https://app.ashna.ai/signin"
            loading="lazy"
            sandbox="allow-same-origin allow-scripts allow-forms"
            referrerpolicy="no-referrer">
        </iframe>
    </div>
</div>
