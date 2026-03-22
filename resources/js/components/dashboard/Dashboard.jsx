import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import api from '../../services/api';
import Card from '../ui/Card';

const Spinner = () => (
    <div className="flex items-center justify-center py-20">
        <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
    </div>
);

export default function Dashboard() {
    const { user } = useAuth();
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/dashboard')
            .then(({ data }) => setStats(data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <Spinner />;

    const isStaff = ['maire', 'secretaire', 'agent'].includes(user?.role);

    const statCards = [
        {
            label: 'Doleances',
            value: stats?.doleances_count ?? 0,
            color: 'from-blue-500 to-indigo-600',
            shadow: 'shadow-blue-500/20',
            to: '/doleances',
            icon: (
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            ),
        },
        {
            label: 'Evenements',
            value: stats?.events_count ?? 0,
            color: 'from-purple-500 to-pink-600',
            shadow: 'shadow-purple-500/20',
            to: '/events',
            icon: (
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
            ),
        },
        {
            label: 'Annonces',
            value: stats?.announcements_count ?? 0,
            color: 'from-amber-500 to-orange-600',
            shadow: 'shadow-amber-500/20',
            to: '/announcements',
            icon: (
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59" />
                </svg>
            ),
        },
        {
            label: 'Alertes actives',
            value: stats?.alerts_count ?? 0,
            color: 'from-red-500 to-rose-600',
            shadow: 'shadow-red-500/20',
            to: '/alerts',
            icon: (
                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
            ),
        },
    ];

    if (isStaff) {
        statCards.push(
            {
                label: 'Interventions',
                value: stats?.interventions_count ?? 0,
                color: 'from-emerald-500 to-teal-600',
                shadow: 'shadow-emerald-500/20',
                to: '/interventions',
                icon: (
                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M11.42 15.17l-5.58-3.22a.8.8 0 01-.38-.65V7.87c0-.29.15-.56.38-.65l5.58-3.22a.8.8 0 01.76 0l5.58 3.22c.24.14.38.38.38.65v3.43c0 .27-.15.51-.38.65l-5.58 3.22a.8.8 0 01-.76 0z" />
                    </svg>
                ),
            },
            {
                label: 'Vehicules',
                value: stats?.vehicles_count ?? 0,
                color: 'from-cyan-500 to-blue-600',
                shadow: 'shadow-cyan-500/20',
                to: '/vehicles',
                icon: (
                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193l-2.715-3.81A1.5 1.5 0 0014.19 3H9.81a1.5 1.5 0 00-1.222.632L5.875 7.433a17.902 17.902 0 00-3.213 9.193c-.04.62.468 1.124 1.09 1.124H5.25" />
                    </svg>
                ),
            }
        );
    }

    return (
        <div className="space-y-8">
            {/* Welcome */}
            <div>
                <h1 className="text-2xl sm:text-3xl font-bold text-gray-900">
                    Bonjour, {user?.first_name} !
                </h1>
                <p className="mt-1 text-gray-500">
                    Voici un apercu de votre commune aujourd'hui.
                </p>
            </div>

            {/* Stats grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {statCards.map((stat) => (
                    <Link
                        key={stat.label}
                        to={stat.to}
                        className="group block"
                    >
                        <div className={`relative overflow-hidden rounded-2xl bg-gradient-to-br ${stat.color} p-6 text-white shadow-lg ${stat.shadow} hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5`}>
                            <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2" />
                            <div className="relative">
                                <div className="flex items-center justify-between">
                                    <div className="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                                        {stat.icon}
                                    </div>
                                    <svg className="w-5 h-5 opacity-50 group-hover:opacity-100 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                                <p className="mt-4 text-3xl font-bold">{stat.value}</p>
                                <p className="text-sm text-white/80">{stat.label}</p>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>

            {/* Recent activity */}
            {stats?.recent_doleances && stats.recent_doleances.length > 0 && (
                <Card title="Doleances recentes" actions={<Link to="/doleances" className="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Voir tout</Link>}>
                    <div className="divide-y divide-gray-50">
                        {stats.recent_doleances.map((d) => (
                            <Link key={d.uuid} to={`/doleances/${d.uuid}`} className="flex items-center justify-between py-3 hover:bg-gray-50 -mx-6 px-6 transition-colors">
                                <div className="min-w-0">
                                    <p className="text-sm font-medium text-gray-900 truncate">{d.title}</p>
                                    <p className="text-xs text-gray-500 mt-0.5">{d.category} &middot; {new Date(d.created_at).toLocaleDateString('fr-FR')}</p>
                                </div>
                                <span className={`shrink-0 ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                                    d.status === 'nouvelle' ? 'bg-blue-100 text-blue-700' :
                                    d.status === 'en_cours' ? 'bg-amber-100 text-amber-700' :
                                    d.status === 'resolue' ? 'bg-emerald-100 text-emerald-700' :
                                    'bg-red-100 text-red-700'
                                }`}>
                                    {d.status === 'nouvelle' ? 'Nouvelle' : d.status === 'en_cours' ? 'En cours' : d.status === 'resolue' ? 'Resolue' : 'Rejetee'}
                                </span>
                            </Link>
                        ))}
                    </div>
                </Card>
            )}
        </div>
    );
}
