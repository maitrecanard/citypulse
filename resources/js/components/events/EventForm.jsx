import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

export default function EventForm() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const isEdit = Boolean(uuid);

    const [form, setForm] = useState({
        title: '',
        description: '',
        location: '',
        starts_at: '',
        ends_at: '',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(isEdit);

    useEffect(() => {
        if (isEdit) {
            api.get(`/events/${uuid}`)
                .then(({ data }) => {
                    const e = data.data || data;
                    setForm({
                        title: e.title || '',
                        description: e.description || '',
                        location: e.location || '',
                        starts_at: e.starts_at ? e.starts_at.slice(0, 16) : '',
                        ends_at: e.ends_at ? e.ends_at.slice(0, 16) : '',
                    });
                })
                .catch(() => setGlobalError("Impossible de charger l'evenement."))
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
                await api.put(`/events/${uuid}`, form);
            } else {
                await api.post('/events', form);
            }
            navigate('/events');
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
                    {isEdit ? "Modifier l'evenement" : 'Nouvel evenement'}
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
                        placeholder="Nom de l'evenement"
                        error={errors.title?.[0]}
                        required
                    />
                    <Input
                        label="Description"
                        type="textarea"
                        name="description"
                        value={form.description}
                        onChange={handleChange}
                        placeholder="Decrivez l'evenement..."
                        error={errors.description?.[0]}
                    />
                    <Input
                        label="Lieu"
                        name="location"
                        value={form.location}
                        onChange={handleChange}
                        placeholder="Adresse ou lieu de l'evenement"
                        error={errors.location?.[0]}
                    />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <Input
                            label="Date de debut"
                            type="datetime-local"
                            name="starts_at"
                            value={form.starts_at}
                            onChange={handleChange}
                            error={errors.starts_at?.[0]}
                            required
                        />
                        <Input
                            label="Date de fin"
                            type="datetime-local"
                            name="ends_at"
                            value={form.ends_at}
                            onChange={handleChange}
                            error={errors.ends_at?.[0]}
                        />
                    </div>
                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" loading={loading}>
                            {isEdit ? 'Enregistrer' : "Creer l'evenement"}
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
