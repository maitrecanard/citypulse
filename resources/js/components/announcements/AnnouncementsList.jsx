import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import Button from '../ui/Button';
import Badge from '../ui/Badge';

export default function AnnouncementsList() {
    const { user } = useAuth();
    const [announcements, setAnnouncements] = useState([]);
    const [loading, setLoading] = useState(true);

    const isStaff = ['maire', 'secretaire', 'agent'].includes(user?.role);

    useEffect(() => {
        api.get('/announcements')
            .then(({ data }) => setAnnouncements(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    const priorityBorder = {
        urgente: 'border-l-red-500',
        importante: 'border-l-orange-500',
        normale: 'border-l-blue-500',
    };

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Annonces</h1>
                    <p className="text-gray-500 mt-1">Annonces officielles de la mairie</p>
                </div>
                {isStaff && (
                    <Link to="/announcements/create">
                        <Button>
                            <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nouvelle annonce
                        </Button>
                    </Link>
                )}
            </div>

            {loading && (
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
                </div>
            )}

            {!loading && announcements.length === 0 && (
                <div className="text-center py-20">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-amber-50 flex items-center justify-center mb-4">
                        <svg className="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09" />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">Aucune annonce</h3>
                    <p className="mt-1 text-gray-500">Les annonces de la mairie apparaitront ici.</p>
                </div>
            )}

            {/* Timeline feed */}
            {!loading && announcements.length > 0 && (
                <div className="space-y-4">
                    {announcements.map((a) => (
                        <div
                            key={a.uuid}
                            className={`bg-white rounded-2xl border border-gray-100 border-l-4 ${
                                priorityBorder[a.priority] || 'border-l-blue-500'
                            } p-6 hover:shadow-md transition-shadow duration-200`}
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Badge variant={a.priority || 'normale'} />
                                        <span className="text-xs text-gray-400">
                                            {new Date(a.created_at).toLocaleDateString('fr-FR', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })}
                                        </span>
                                    </div>
                                    <h3 className="text-lg font-semibold text-gray-900">{a.title}</h3>
                                    {a.content && (
                                        <p className="mt-2 text-gray-600 whitespace-pre-wrap leading-relaxed">{a.content}</p>
                                    )}
                                </div>
                                {isStaff && (
                                    <Link
                                        to={`/announcements/${a.uuid}/edit`}
                                        className="shrink-0 p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors"
                                    >
                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                    </Link>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
