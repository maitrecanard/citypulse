import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Badge from '../ui/Badge';

const statusLabels = {
    disponible: 'Disponible',
    en_mission: 'En mission',
    en_maintenance: 'Maintenance',
    hors_service: 'Hors service',
};

export default function VehiclesList() {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/vehicles')
            .then(({ data }) => setVehicles(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Vehicules</h1>
                    <p className="text-gray-500 mt-1">Gestion de la flotte municipale</p>
                </div>
                <Link to="/vehicles/create">
                    <Button>
                        <svg className="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Ajouter un vehicule
                    </Button>
                </Link>
            </div>

            {loading && (
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
                </div>
            )}

            {!loading && vehicles.length === 0 && (
                <div className="text-center py-20">
                    <div className="w-16 h-16 mx-auto rounded-2xl bg-cyan-50 flex items-center justify-center mb-4">
                        <svg className="w-8 h-8 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193l-2.715-3.81A1.5 1.5 0 0014.19 3H9.81a1.5 1.5 0 00-1.222.632L5.875 7.433a17.902 17.902 0 00-3.213 9.193c-.04.62.468 1.124 1.09 1.124H5.25" />
                        </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900">Aucun vehicule</h3>
                    <p className="mt-1 text-gray-500">Ajoutez des vehicules a votre flotte municipale.</p>
                </div>
            )}

            {!loading && vehicles.length > 0 && (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    {vehicles.map((v) => (
                        <Link
                            key={v.uuid}
                            to={`/vehicles/${v.uuid}/edit`}
                            className="group bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-lg hover:border-cyan-100 transition-all duration-300 hover:-translate-y-0.5"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-50 to-blue-50 flex items-center justify-center text-cyan-600">
                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193l-2.715-3.81A1.5 1.5 0 0014.19 3H9.81a1.5 1.5 0 00-1.222.632L5.875 7.433a17.902 17.902 0 00-3.213 9.193c-.04.62.468 1.124 1.09 1.124H5.25" />
                                    </svg>
                                </div>
                                <Badge variant={v.status || 'disponible'} />
                            </div>
                            <h3 className="mt-4 text-base font-semibold text-gray-900 group-hover:text-cyan-600 transition-colors">
                                {v.name}
                            </h3>
                            <div className="mt-3 space-y-1.5 text-sm text-gray-500">
                                {v.type && (
                                    <p className="capitalize">{v.type}</p>
                                )}
                                {v.plate_number && (
                                    <p className="font-mono text-xs bg-gray-50 inline-block px-2 py-0.5 rounded">{v.plate_number}</p>
                                )}
                                {v.team && (
                                    <p className="flex items-center gap-1">
                                        <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                        </svg>
                                        {v.team}
                                    </p>
                                )}
                            </div>
                            {v.next_maintenance_date && (
                                <div className="mt-4 pt-3 border-t border-gray-50 text-xs text-gray-400">
                                    Prochaine maintenance : {new Date(v.next_maintenance_date).toLocaleDateString('fr-FR')}
                                </div>
                            )}
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
