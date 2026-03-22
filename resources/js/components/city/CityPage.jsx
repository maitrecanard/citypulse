import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../../services/api';
import Badge from '../ui/Badge';

export default function CityPage() {
    const { uuid } = useParams();
    const [city, setCity] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get(`/cities/${uuid}/public`)
            .then(({ data }) => setCity(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [uuid]);

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
            </div>
        );
    }

    if (!city) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
                <div className="text-center">
                    <h1 className="text-2xl font-bold text-gray-900">Commune introuvable</h1>
                    <p className="mt-2 text-gray-500">Cette page de commune n'existe pas.</p>
                    <Link
                        to="/"
                        className="inline-flex items-center gap-2 mt-6 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl"
                    >
                        Retour a l'accueil
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-white">
            {/* Header */}
            <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                    <Link to="/" className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
                            <svg className="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3" />
                            </svg>
                        </div>
                        <span className="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            CityPulse
                        </span>
                    </Link>
                    <div className="flex items-center gap-3">
                        <Link to="/login" className="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                            Connexion
                        </Link>
                        <Link
                            to="/register"
                            className="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25"
                        >
                            Rejoindre
                        </Link>
                    </div>
                </div>
            </nav>

            {/* Hero */}
            <section className="relative py-20 sm:py-28 overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-purple-50" />
                <div className="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-indigo-400/15 to-purple-400/15 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" />
                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        {city.name}
                    </h1>
                    {city.description && (
                        <p className="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">{city.description}</p>
                    )}
                    {(city.population || city.department) && (
                        <div className="mt-6 flex items-center justify-center gap-6 text-sm text-gray-500">
                            {city.population && (
                                <span className="flex items-center gap-1.5">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    {city.population.toLocaleString('fr-FR')} habitants
                                </span>
                            )}
                            {city.department && <span>{city.department}</span>}
                        </div>
                    )}
                </div>
            </section>

            {/* Recent events */}
            {city.events && city.events.length > 0 && (
                <section className="py-16 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-8">Evenements a venir</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {city.events.map((event) => (
                                <div key={event.uuid} className="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
                                    <div className="h-32 bg-gradient-to-br from-purple-400 to-indigo-500" />
                                    <div className="p-5">
                                        <h3 className="font-semibold text-gray-900">{event.title}</h3>
                                        {event.starts_at && (
                                            <p className="mt-1 text-sm text-gray-500">
                                                {new Date(event.starts_at).toLocaleDateString('fr-FR', {
                                                    day: 'numeric',
                                                    month: 'long',
                                                    year: 'numeric',
                                                })}
                                            </p>
                                        )}
                                        {event.location && (
                                            <p className="mt-1 text-sm text-gray-400">{event.location}</p>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Announcements */}
            {city.announcements && city.announcements.length > 0 && (
                <section className="py-16 bg-gray-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-8">Dernieres annonces</h2>
                        <div className="space-y-4">
                            {city.announcements.map((a) => (
                                <div key={a.uuid} className="bg-white rounded-2xl border border-gray-100 p-6">
                                    <div className="flex items-center gap-2 mb-2">
                                        <h3 className="font-semibold text-gray-900">{a.title}</h3>
                                        {a.priority && <Badge variant={a.priority} />}
                                    </div>
                                    {a.content && <p className="text-gray-600 text-sm">{a.content}</p>}
                                    <p className="mt-2 text-xs text-gray-400">
                                        {new Date(a.created_at).toLocaleDateString('fr-FR', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric',
                                        })}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Active alerts */}
            {city.alerts && city.alerts.length > 0 && (
                <section className="py-16 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 className="text-2xl font-bold text-gray-900 mb-8">Alertes en cours</h2>
                        <div className="space-y-4">
                            {city.alerts.map((alert) => (
                                <div
                                    key={alert.uuid}
                                    className={`rounded-2xl p-6 border ${
                                        alert.severity === 'critical'
                                            ? 'bg-red-50 border-red-200'
                                            : alert.severity === 'warning'
                                            ? 'bg-amber-50 border-amber-200'
                                            : 'bg-blue-50 border-blue-200'
                                    }`}
                                >
                                    <div className="flex items-center gap-2 mb-1">
                                        <h3 className="font-semibold text-gray-900">{alert.title}</h3>
                                        <Badge variant={alert.severity || 'info'} />
                                    </div>
                                    {alert.description && (
                                        <p className="text-sm text-gray-700">{alert.description}</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* CTA */}
            <section className="py-20 bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700">
                <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white">Rejoignez votre commune sur CityPulse</h2>
                    <p className="mt-4 text-lg text-indigo-100">
                        Creez votre compte pour suivre les actualites, signaler des problemes et participer a la vie de votre commune.
                    </p>
                    <div className="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            to="/register"
                            className="px-8 py-4 text-base font-semibold text-indigo-700 bg-white rounded-xl hover:bg-indigo-50 transition-all shadow-lg"
                        >
                            Creer un compte
                        </Link>
                        <Link
                            to="/login"
                            className="px-8 py-4 text-base font-semibold text-white border-2 border-white/30 rounded-xl hover:bg-white/10 transition-all"
                        >
                            Se connecter
                        </Link>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="py-8 border-t border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                    Propulse par CityPulse &middot; 2026 Tous droits reserves.
                </div>
            </footer>
        </div>
    );
}
