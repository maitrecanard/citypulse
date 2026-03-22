import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import Button from '../ui/Button';
import Badge from '../ui/Badge';
import Input from '../ui/Input';

const categories = [
    { value: '', label: 'Toutes les categories' },
    { value: 'voirie', label: 'Voirie' },
    { value: 'eclairage', label: 'Eclairage' },
    { value: 'proprete', label: 'Proprete' },
    { value: 'bruit', label: 'Bruit' },
    { value: 'securite', label: 'Securite' },
    { value: 'autre', label: 'Autre' },
];

const statuses = [
    { value: '', label: 'Tous les statuts' },
    { value: 'nouvelle', label: 'Nouvelle' },
    { value: 'en_cours', label: 'En cours' },
    { value: 'resolue', label: 'Resolue' },
    { value: 'rejetee', label: 'Rejetee' },
];

export default function DoleancesList() {
    const { user } = useAuth();
    const [doleances, setDoleances] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('');
    const [statusFilter, setStatusFilter] = useState('');

    useEffect(() => {
        api.get('/doleances')
            .then(({ data }) => setDoleances(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    const filtered = doleances.filter((d) => {
        const matchSearch = !search || d.title.toLowerCase().includes(search.toLowerCase()) || d.description?.toLowerCase().includes(search.toLowerCase());
        const matchCategory = !categoryFilter || d.category === categoryFilter;
        const matchStatus = !statusFilter || d.status === statusFilter;
        return matchSearch && matchCategory && matchStatus;
    });

    const canCreate = user?.role !== undefined;

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Doleances</h1>
                    <p className="text-gray-500 mt-1">Signalements et demandes des citoyens</p>
                </div>
                {canCreate && (
                    <Link to="/doleances/create">
                        <Button>
                            <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Nouvelle doleance
                        </Button>
                    </Link>
                )}
            </div>

            {/* Filters */}
            <div className="flex flex-col sm:flex-row gap-3">
                <div className="flex-1">
                    <Input
                        placeholder="Rechercher une doleance..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
                <Input
                    type="select"
                    value={categoryFilter}
                    onChange={(e) => setCategoryFilter(e.target.value)}
                    className="sm:w-48"
                >
                    {categories.map((c) => (
                        <option key={c.value} value={c.value}>{c.label}</option>
                    ))}
                </Input>
                <Input
                    type="select"
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="sm:w-44"
                >
                    {statuses.map((s) => (
                        <option key={s.value} value={s.value}>{s.label}</option>
                    ))}
                </Input>
            </div>

            {/* Loading */}
            {loading && (
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
                </div>
            )}

            {/* Empty state */}
            {!loading && filtered.length === 0 && (
                <div className="text-center py-20">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
                        <svg className="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9.75m3 0h.008v.008H12.75V15zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">Aucune doleance</h3>
                    <p className="mt-1 text-gray-500">
                        {search || categoryFilter || statusFilter
                            ? 'Aucun resultat pour vos criteres de recherche.'
                            : 'Les doleances des citoyens apparaitront ici.'}
                    </p>
                </div>
            )}

            {/* Cards grid */}
            {!loading && filtered.length > 0 && (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {filtered.map((d) => (
                        <Link
                            key={d.uuid}
                            to={`/doleances/${d.uuid}`}
                            className="group bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-lg hover:border-indigo-100 transition-all duration-300 hover:-translate-y-0.5"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <h3 className="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                    {d.title}
                                </h3>
                                <Badge variant={d.status} />
                            </div>
                            {d.description && (
                                <p className="mt-2 text-sm text-gray-500 line-clamp-2">{d.description}</p>
                            )}
                            <div className="mt-4 flex items-center gap-3 text-xs text-gray-400">
                                <span className="inline-flex items-center gap-1 capitalize">
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    </svg>
                                    {d.category}
                                </span>
                                <span className="inline-flex items-center gap-1">
                                    <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {new Date(d.created_at).toLocaleDateString('fr-FR')}
                                </span>
                                {d.priority && (
                                    <Badge variant={d.priority} className="ml-auto" />
                                )}
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
