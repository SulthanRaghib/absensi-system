<style>
    body {
        box-sizing: border-box;
    }

    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .app-wrapper {
        min-height: 100vh;
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .login-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        width: 100%;
        max-width: 440px;
        padding: 2rem 2.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-icon {
        width: 100px;
        height: 100px;
        fill: white;
    }

    .form-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
        background: #f9fafb;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .input-wrapper {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        fill: #9ca3af;
        transition: fill 0.2s ease;
    }

    .input-wrapper:focus-within .input-icon {
        fill: #667eea;
    }

    .primary-button {
        width: 100%;
        padding: 0.9375rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .primary-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
    }

    .primary-button:active {
        transform: translateY(0);
    }

    .primary-button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .secondary-button {
        width: 100%;
        padding: 0.9375rem;
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 0.75rem;
    }

    .secondary-button:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        margin: 1.25rem 0;
    }

    .custom-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .custom-checkbox:checked {
        background: #667eea;
        border-color: #667eea;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .password-toggle:hover {
        opacity: 0.7;
    }

    .eye-icon {
        width: 20px;
        height: 20px;
        fill: #9ca3af;
    }

    .message-area {
        padding: 0.875rem;
        border-radius: 10px;
        margin-bottom: 1.25rem;
        font-size: 0.875rem;
    }

    .message-area.success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .message-area.error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    @media (max-width: 640px) {
        .login-card {
            padding: 2rem 1.5rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
        }
    }

    .map-container {
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
    }
</style>
