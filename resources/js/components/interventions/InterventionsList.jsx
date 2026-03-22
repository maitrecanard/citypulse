import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Badge from '../ui/Badge';

export default function InterventionsList() {
    const [interventions, setInterventions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/interventions')
            .then(({ data }) => setInterventions(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    const formatDate = (date) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Interventions</h1>
                    <p className="text-gray-500 mt-1">Planification et suivi des interventions</p>
                </div>
                <Link to="/interventions/create">
                    <Button>
                        <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nouvelle intervention
                    </Button>
                </Link>
            </div>

            {loading && (
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
                </div>
            )}

            {!loading && interventions.length === 0 && (
                <div className="text-center py-20">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                        <svg className="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M11.42 15.17l-5.58-3.22a.8.8 0 01-.38-.65V7.87c0-.29.15-.56.38-.65l5.58-3.22a.8.8 0 01.76 0l5.58 3.22c.24.14.38.38.38.65v3.43c0 .27-.15.51-.38.65l-5.58 3.22a.8.8 0 01-.76 0z" />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">Aucune intervention</h3>
                    <p className="mt-1 text-gray-500">Les interventions planifiees apparaitront ici.</p>
                </div>
            )}

            {/* Desktop table */}
            {!loading && interventions.length > 0 && (
                <>
                    <div className="hidden lg:block bg-white rounded-2xl border border-gray-100 overflow-hidden">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-gray-50">
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Titre</th>
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Statut</th>
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Priorite</th>
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Agent</th>
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Vehicule</th>
                                    <th className="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-4">Date</th>
                                    <th className="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {interventions.map((i) => (
                                    <tr key={i.uuid} className="hover:bg-gray-50/50 transition-colors">
                                        <td className="px-6 py-4">
                                            <p className="text-sm font-medium text-gray-900">{i.title}</p>
                                        </td>
                                        <td className="px-6 py-4">
                                            <Badge variant={i.status || 'default'} />
                                        </td>
                                        <td className="px-6 py-4">
                                            <Badge variant={i.priority || 'normale'} />
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {i.assigned_user ? `${i.assigned_user.first_name} ${i.assigned_user.last_name}` : '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {i.vehicle?.name || i.vehicle?.plate_number || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-500">
                                            {formatDate(i.scheduled_at)}
                                        </td>
                                        <td className="px-6 py-4">
                                            <Link
                                                to={`/interventions/${i.uuid}/edit`}
                                                className="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
                                            >
                                                Modifier
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Mobile cards */}
                    <div className="lg:hidden space-y-4">
                        {interventions.map((i) => (
                            <Link
                                key={i.uuid}
                                to={`/interventions/${i.uuid}/edit`}
                                className="block bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition-shadow"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <h3 className="text-sm font-semibold text-gray-900">{i.title}</h3>
                                    <Badge variant={i.status || 'default'} />
                                </div>
                                <div className="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                    {i.priority && <Badge variant={i.priority} />}
                                    <span>{formatDate(i.scheduled_at)}</span>
                                    {i.assigned_user && (
                                        <span>{i.assigned_user.first_name} {i.assigned_user.last_name}</span>
                                    )}
                                </div>
                            </Link>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
