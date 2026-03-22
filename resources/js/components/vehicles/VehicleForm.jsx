import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

const vehicleTypes = [
    { value: 'voiture', label: 'Voiture' },
    { value: 'camion', label: 'Camion' },
    { value: 'utilitaire', label: 'Utilitaire' },
    { value: 'engin', label: 'Engin' },
    { value: 'deux_roues', label: 'Deux roues' },
    { value: 'autre', label: 'Autre' },
];

export default function VehicleForm() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const isEdit = Boolean(uuid);

    const [form, setForm] = useState({
        name: '',
        type: 'voiture',
        plate_number: '',
        team: '',
        next_maintenance_date: '',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(isEdit);
    const [maintenanceHistory, setMaintenanceHistory] = useState([]);

    useEffect(() => {
        if (isEdit) {
            api.get(`/vehicles/${uuid}`)
                .then(({ data }) => {
                    const v = data.data || data;
                    setForm({
                        name: v.name || '',
                        type: v.type || 'voiture',
                        plate_number: v.plate_number || '',
                        team: v.team || '',
                        next_maintenance_date: v.next_maintenance_date ? v.next_maintenance_date.slice(0, 10) : '',
                    });
                    if (v.maintenance_history) {
                        setMaintenanceHistory(v.maintenance_history);
                    }
                })
                .catch(() => setGlobalError('Impossible de charger le vehicule.'))
                .finally(() => setFetching(false));
        }
    }, [uuid, isEdit]);

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
        setErrors({ ...errors, [e.target.name]: '' });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setGlobalError('');
        setErrors({});
        setLoading(true);
        try {
            if (isEdit) {
                await api.put(`/vehicles/${uuid}`, form);
            } else {
                await api.post('/vehicles', form);
            }
            navigate('/vehicles');
        } catch (err) {
            const data = err.response?.data;
            if (data?.errors) setErrors(data.errors);
            else setGlobalError(data?.message || 'Une erreur est survenue.');
        } finally {
            setLoading(false);
        }
    };

    if (fetching) {
        return (
            <div className="flex items-center justify-center py-20">
                <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto space-y-6">
            <div>
                <button
                    onClick={() => navigate(-1)}
                    className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Retour
                </button>
                <h1 className="text-2xl font-bold text-gray-900 mt-2">
                    {isEdit ? 'Modifier le vehicule' : 'Ajouter un vehicule'}
                </h1>
            </div>

            <Card>
                {globalError && (
                    <div className="mb-6"><Alert type="error">{globalError}</Alert></div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5">
                    <Input
                        label="Nom"
                        name="name"
                        value={form.name}
                        onChange={handleChange}
                        placeholder="Ex: Kangoo Services Techniques"
                        error={errors.name?.[0]}
                        required
                    />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Type"
                            type="select"
                            name="type"
                            value={form.type}
                            onChange={handleChange}
                            error={errors.type?.[0]}
                        >
                            {vehicleTypes.map((t) => (
                                <option key={t.value} value={t.value}>{t.label}</option>
                            ))}
                        </Input>
                        <Input
                            label="Immatriculation"
                            name="plate_number"
                            value={form.plate_number}
                            onChange={handleChange}
                            placeholder="AA-123-BB"
                            error={errors.plate_number?.[0]}
                            required
                        />
                    </div>
                    <Input
                        label="Equipe"
                        name="team"
                        value={form.team}
                        onChange={handleChange}
                        placeholder="Ex: Services Techniques"
                        error={errors.team?.[0]}
                    />
                    <Input
                        label="Prochaine maintenance"
                        type="date"
                        name="next_maintenance_date"
                        value={form.next_maintenance_date}
                        onChange={handleChange}
                        error={errors.next_maintenance_date?.[0]}
                    />
                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" loading={loading}>
                            {isEdit ? 'Enregistrer' : 'Ajouter le vehicule'}
                        </Button>
                        <Button type="button" variant="secondary" onClick={() => navigate(-1)}>
                            Annuler
                        </Button>
                    </div>
                </form>
            </Card>

            {/* Maintenance history */}
            {isEdit && maintenanceHistory.length > 0 && (
                <Card title="Historique de maintenance">
                    <div className="divide-y divide-gray-50">
                        {maintenanceHistory.map((m, idx) => (
                            <div key={idx} className="py-3 flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-900">{m.description || 'Maintenance'}</p>
                                    <p className="text-xs text-gray-500">{m.type || 'Generale'}</p>
                                </div>
                                <span className="text-sm text-gray-500">
                                    {m.date ? new Date(m.date).toLocaleDateString('fr-FR') : '-'}
                                </span>
                            </div>
                        ))}
                    </div>
                </Card>
            )}

            {isEdit && maintenanceHistory.length === 0 && (
                <Card title="Historique de maintenance">
                    <p className="text-sm text-gray-500 text-center py-4">Aucun historique de maintenance enregistre.</p>
                </Card>
            )}
        </div>
    );
}
