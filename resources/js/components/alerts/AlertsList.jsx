import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import Button from '../ui/Button';
import Badge from '../ui/Badge';

const severityConfig = {
    critical: { bg: 'bg-red-50', border: 'border-red-200', icon: 'text-red-500' },
    warning: { bg: 'bg-amber-50', border: 'border-amber-200', icon: 'text-amber-500' },
    info: { bg: 'bg-blue-50', border: 'border-blue-200', icon: 'text-blue-500' },
};

const typeLabels = {
    securite: 'Securite',
    meteo: 'Meteo',
    travaux: 'Travaux',
    autre: 'Autre',
};

export default function AlertsList() {
    const { user } = useAuth();
    const [alerts, setAlerts] = useState([]);
    const [loading, setLoading] = useState(true);

    const isStaff = ['maire', 'secretaire', 'agent'].includes(user?.role);

    useEffect(() => {
        api.get('/alerts')
            .then(({ data }) => setAlerts(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Alertes</h1>
                    <p className="text-gray-500 mt-1">Alertes et informations importantes</p>
                </div>
                {isStaff && (
                    <Link to="/alerts/create">
                        <Button>
                            <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nouvelle alerte
                        </Button>
                    </Link>
                )}
            </div>

            {loading && (
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
                </div>
            )}

            {!loading && alerts.length === 0 && (
                <div className="text-center py-20">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                        <svg className="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">Aucune alerte active</h3>
                    <p className="mt-1 text-gray-500">Tout est calme ! Les alertes apparaitront ici.</p>
                </div>
            )}

            {!loading && alerts.length > 0 && (
                <div className="space-y-4">
                    {alerts.map((alert) => {
                        const config = severityConfig[alert.severity] || severityConfig.info;
                        return (
                            <div
                                key={alert.uuid}
                                className={`${config.bg} border ${config.border} rounded-2xl p-6 transition-shadow hover:shadow-md`}
                            >
                                <div className="flex items-start gap-4">
                                    <div className={`shrink-0 mt-0.5 ${config.icon}`}>
                                        {alert.severity === 'critical' ? (
                                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                        ) : alert.severity === 'warning' ? (
                                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zm0-13.5a9 9 0 110 18 9 9 0 010-18z" />
                                            </svg>
                                        ) : (
                                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                            </svg>
                                        )}
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 className="text-base font-semibold text-gray-900">{alert.title}</h3>
                                            <Badge variant={alert.severity || 'info'} />
                                            {alert.type && (
                                                <span className="text-xs text-gray-500 bg-white/60 px-2 py-0.5 rounded-full">
                                                    {typeLabels[alert.type] || alert.type}
                                                </span>
                                            )}
                                        </div>
                                        {alert.description && (
                                            <p className="text-sm text-gray-700 mt-1 whitespace-pre-wrap">{alert.description}</p>
                                        )}
                                        <p className="text-xs text-gray-500 mt-3">
                                            {new Date(alert.created_at).toLocaleDateString('fr-FR', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </p>
                                    </div>
                                    {isStaff && (
                                        <Link
                                            to={`/alerts/${alert.uuid}/edit`}
                                            className="shrink-0 p-2 rounded-lg hover:bg-white/50 text-gray-400 hover:text-gray-600 transition-colors"
                                        >
                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                        </Link>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
