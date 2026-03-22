import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

const statusOptions = [
    { value: 'planifiee', label: 'Planifiee' },
    { value: 'en_cours', label: 'En cours' },
    { value: 'terminee', label: 'Terminee' },
    { value: 'annulee', label: 'Annulee' },
];

const priorities = [
    { value: 'basse', label: 'Basse' },
    { value: 'normale', label: 'Normale' },
    { value: 'haute', label: 'Haute' },
    { value: 'urgente', label: 'Urgente' },
];

export default function InterventionForm() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const isEdit = Boolean(uuid);

    const [form, setForm] = useState({
        title: '',
        description: '',
        scheduled_at: '',
        assigned_to: '',
        vehicle_uuid: '',
        status: 'planifiee',
        priority: 'normale',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(isEdit);
    const [agents, setAgents] = useState([]);
    const [vehicles, setVehicles] = useState([]);

    useEffect(() => {
        // Load agents and vehicles for select fields
        Promise.all([
            api.get('/users?role=agent').catch(() => ({ data: [] })),
            api.get('/vehicles').catch(() => ({ data: [] })),
        ]).then(([agentsRes, vehiclesRes]) => {
            setAgents(agentsRes.data?.data || agentsRes.data || []);
            setVehicles(vehiclesRes.data?.data || vehiclesRes.data || []);
        });

        if (isEdit) {
            api.get(`/interventions/${uuid}`)
                .then(({ data }) => {
                    const i = data.data || data;
                    setForm({
                        title: i.title || '',
                        description: i.description || '',
                        scheduled_at: i.scheduled_at ? i.scheduled_at.slice(0, 16) : '',
                        assigned_to: i.assigned_to || i.assigned_user?.uuid || '',
                        vehicle_uuid: i.vehicle_uuid || i.vehicle?.uuid || '',
                        status: i.status || 'planifiee',
                        priority: i.priority || 'normale',
                    });
                })
                .catch(() => setGlobalError("Impossible de charger l'intervention."))
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
            const payload = { ...form };
            if (!payload.assigned_to) delete payload.assigned_to;
            if (!payload.vehicle_uuid) delete payload.vehicle_uuid;

            if (isEdit) {
                await api.put(`/interventions/${uuid}`, payload);
            } else {
                await api.post('/interventions', payload);
            }
            navigate('/interventions');
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
        <div className="max-w-2xl mx-auto">
            <div className="mb-6">
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
                    {isEdit ? "Modifier l'intervention" : 'Nouvelle intervention'}
                </h1>
            </div>

            <Card>
                {globalError && (
                    <div className="mb-6"><Alert type="error">{globalError}</Alert></div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5">
                    <Input
                        label="Titre"
                        name="title"
                        value={form.title}
                        onChange={handleChange}
                        placeholder="Description de l'intervention"
                        error={errors.title?.[0]}
                        required
                    />
                    <Input
                        label="Description"
                        type="textarea"
                        name="description"
                        value={form.description}
                        onChange={handleChange}
                        placeholder="Details de l'intervention..."
                        error={errors.description?.[0]}
                    />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Statut"
                            type="select"
                            name="status"
                            value={form.status}
                            onChange={handleChange}
                            error={errors.status?.[0]}
                        >
                            {statusOptions.map((s) => (
                                <option key={s.value} value={s.value}>{s.label}</option>
                            ))}
                        </Input>
                        <Input
                            label="Priorite"
                            type="select"
                            name="priority"
                            value={form.priority}
                            onChange={handleChange}
                            error={errors.priority?.[0]}
                        >
                            {priorities.map((p) => (
                                <option key={p.value} value={p.value}>{p.label}</option>
                            ))}
                        </Input>
                    </div>
                    <Input
                        label="Date planifiee"
                        type="datetime-local"
                        name="scheduled_at"
                        value={form.scheduled_at}
                        onChange={handleChange}
                        error={errors.scheduled_at?.[0]}
                    />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Agent assigne"
                            type="select"
                            name="assigned_to"
                            value={form.assigned_to}
                            onChange={handleChange}
                            error={errors.assigned_to?.[0]}
                        >
                            <option value="">Non assigne</option>
                            {agents.map((a) => (
                                <option key={a.uuid} value={a.uuid}>
                                    {a.first_name} {a.last_name}
                                </option>
                            ))}
                        </Input>
                        <Input
                            label="Vehicule"
                            type="select"
                            name="vehicle_uuid"
                            value={form.vehicle_uuid}
                            onChange={handleChange}
                            error={errors.vehicle_uuid?.[0]}
                        >
                            <option value="">Aucun vehicule</option>
                            {vehicles.map((v) => (
                                <option key={v.uuid} value={v.uuid}>
                                    {v.name} ({v.plate_number})
                                </option>
                            ))}
                        </Input>
                    </div>
                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" loading={loading}>
                            {isEdit ? 'Enregistrer' : "Creer l'intervention"}
                        </Button>
                        <Button type="button" variant="secondary" onClick={() => navigate(-1)}>
                            Annuler
                        </Button>
                    </div>
                </form>
            </Card>
        </div>
    );
}
