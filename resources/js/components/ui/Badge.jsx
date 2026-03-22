const variants = {
    nouvelle: 'bg-blue-100 text-blue-700 ring-blue-600/20',
    en_cours: 'bg-amber-100 text-amber-700 ring-amber-600/20',
    resolue: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    rejetee: 'bg-red-100 text-red-700 ring-red-600/20',
    info: 'bg-sky-100 text-sky-700 ring-sky-600/20',
    warning: 'bg-orange-100 text-orange-700 ring-orange-600/20',
    critical: 'bg-red-100 text-red-700 ring-red-600/20',
    success: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    default: 'bg-gray-100 text-gray-700 ring-gray-600/20',
    normale: 'bg-blue-100 text-blue-700 ring-blue-600/20',
    importante: 'bg-orange-100 text-orange-700 ring-orange-600/20',
    urgente: 'bg-red-100 text-red-700 ring-red-600/20',
    basse: 'bg-gray-100 text-gray-600 ring-gray-500/20',
    haute: 'bg-orange-100 text-orange-700 ring-orange-600/20',
    disponible: 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
    en_mission: 'bg-blue-100 text-blue-700 ring-blue-600/20',
    en_maintenance: 'bg-amber-100 text-amber-700 ring-amber-600/20',
    hors_service: 'bg-red-100 text-red-700 ring-red-600/20',
};

const labels = {
    nouvelle: 'Nouvelle',
    en_cours: 'En cours',
    resolue: 'Résolue',
    rejetee: 'Rejetée',
    basse: 'Basse',
    normale: 'Normale',
    haute: 'Haute',
    urgente: 'Urgente',
    importante: 'Importante',
    disponible: 'Disponible',
    en_mission: 'En mission',
    en_maintenance: 'Maintenance',
    hors_service: 'Hors service',
};

export default function Badge({ variant = 'default', children, className = '' }) {
    const style = variants[variant] || variants.default;
    const label = children || labels[variant] || variant;

    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${style} ${className}`}
        >
            {label}
        </span>
    );
}
