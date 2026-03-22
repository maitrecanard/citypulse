export default function Card({ title, actions, children, className = '', padding = true }) {
    return (
        <div
            className={`bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-shadow duration-200 hover:shadow-md ${className}`}
        >
            {(title || actions) && (
                <div className="flex items-center justify-between px-6 py-4 border-b border-gray-50">
                    {title && <h3 className="text-lg font-semibold text-gray-900">{title}</h3>}
                    {actions && <div className="flex items-center gap-2">{actions}</div>}
                </div>
            )}
            <div className={padding ? 'p-6' : ''}>{children}</div>
        </div>
    );
}
