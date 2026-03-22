import { forwardRef } from 'react';

const Input = forwardRef(function Input(
    { label, error, type = 'text', className = '', id, ...rest },
    ref
) {
    const inputId = id || label?.toLowerCase().replace(/\s+/g, '-');

    if (type === 'textarea') {
        return (
            <div className={className}>
                {label && (
                    <label htmlFor={inputId} className="block text-sm font-medium text-gray-700 mb-1.5">
                        {label}
                    </label>
                )}
                <textarea
                    ref={ref}
                    id={inputId}
                    className={`block w-full rounded-xl border px-4 py-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent ${
                        error
                            ? 'border-red-300 bg-red-50 text-red-900 placeholder-red-400'
                            : 'border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400 hover:border-gray-300'
                    }`}
                    rows={4}
                    {...rest}
                />
                {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
            </div>
        );
    }

    if (type === 'select') {
        return (
            <div className={className}>
                {label && (
                    <label htmlFor={inputId} className="block text-sm font-medium text-gray-700 mb-1.5">
                        {label}
                    </label>
                )}
                <select
                    ref={ref}
                    id={inputId}
                    className={`block w-full rounded-xl border px-4 py-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent ${
                        error
                            ? 'border-red-300 bg-red-50 text-red-900'
                            : 'border-gray-200 bg-gray-50 text-gray-900 hover:border-gray-300'
                    }`}
                    {...rest}
                />
                {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
            </div>
        );
    }

    return (
        <div className={className}>
            {label && (
                <label htmlFor={inputId} className="block text-sm font-medium text-gray-700 mb-1.5">
                    {label}
                </label>
            )}
            <input
                ref={ref}
                id={inputId}
                type={type}
                className={`block w-full rounded-xl border px-4 py-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent ${
                    error
                        ? 'border-red-300 bg-red-50 text-red-900 placeholder-red-400'
                        : 'border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400 hover:border-gray-300'
                }`}
                {...rest}
            />
            {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
        </div>
    );
});

export default Input;
