import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

const categories = [
    { value: 'voirie', label: 'Voirie' },
    { value: 'eclairage', label: 'Eclairage' },
    { value: 'proprete', label: 'Proprete' },
    { value: 'bruit', label: 'Bruit' },
    { value: 'securite', label: 'Securite' },
    { value: 'autre', label: 'Autre' },
];

const priorities = [
    { value: 'basse', label: 'Basse' },
    { value: 'normale', label: 'Normale' },
    { value: 'haute', label: 'Haute' },
    { value: 'urgente', label: 'Urgente' },
];

export default function DoleanceForm() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const isEdit = Boolean(uuid);

    const [form, setForm] = useState({
        title: '',
        description: '',
        category: 'voirie',
        priority: 'normale',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(isEdit);

    useEffect(() => {
        if (isEdit) {
            api.get(`/doleances/${uuid}`)
                .then(({ data }) => {
                    const d = data.data || data;
                    setForm({
                        title: d.title || '',
                        description: d.description || '',
                        category: d.category || 'voirie',
                        priority: d.priority || 'normale',
                    });
                })
                .catch(() => setGlobalError('Impossible de charger la doleance.'))
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
                await api.put(`/doleances/${uuid}`, form);
            } else {
                await api.post('/doleances', form);
            }
            navigate('/doleances');
        } catch (err) {
            const data = err.response?.data;
            if (data?.errors) {
                setErrors(data.errors);
            } else {
                setGlobalError(data?.message || 'Une erreur est survenue.');
            }
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
                    {isEdit ? 'Modifier la doleance' : 'Nouvelle doleance'}
                </h1>
            </div>

            <Card>
                {globalError && (
                    <div className="mb-6">
                        <Alert type="error">{globalError}</Alert>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5">
                    <Input
                        label="Titre"
                        name="title"
                        value={form.title}
                        onChange={handleChange}
                        placeholder="Decrivez brievement le probleme"
                        error={errors.title?.[0]}
                        required
                    />
                    <Input
                        label="Description"
                        type="textarea"
                        name="description"
                        value={form.description}
                        onChange={handleChange}
                        placeholder="Donnez plus de details sur le probleme rencontre..."
                        error={errors.description?.[0]}
                    />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Categorie"
                            type="select"
                            name="category"
                            value={form.category}
                            onChange={handleChange}
                            error={errors.category?.[0]}
                        >
                            {categories.map((c) => (
                                <option key={c.value} value={c.value}>{c.label}</option>
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
                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" loading={loading}>
                            {isEdit ? 'Enregistrer' : 'Soumettre la doleance'}
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
