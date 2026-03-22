import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../../services/api';
import Button from '../ui/Button';
import Input from '../ui/Input';
import Alert from '../ui/Alert';
import Card from '../ui/Card';

const priorities = [
    { value: 'normale', label: 'Normale' },
    { value: 'importante', label: 'Importante' },
    { value: 'urgente', label: 'Urgente' },
];

export default function AnnouncementForm() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const isEdit = Boolean(uuid);

    const [form, setForm] = useState({
        title: '',
        content: '',
        priority: 'normale',
    });
    const [errors, setErrors] = useState({});
    const [globalError, setGlobalError] = useState('');
    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(isEdit);

    useEffect(() => {
        if (isEdit) {
            api.get(`/announcements/${uuid}`)
                .then(({ data }) => {
                    const a = data.data || data;
                    setForm({
                        title: a.title || '',
                        content: a.content || '',
                        priority: a.priority || 'normale',
                    });
                })
                .catch(() => setGlobalError("Impossible de charger l'annonce."))
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
                await api.put(`/announcements/${uuid}`, form);
            } else {
                await api.post('/announcements', form);
            }
            navigate('/announcements');
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
                    {isEdit ? "Modifier l'annonce" : 'Nouvelle annonce'}
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
                        placeholder="Titre de l'annonce"
                        error={errors.title?.[0]}
                        required
                    />
                    <Input
                        label="Contenu"
                        type="textarea"
                        name="content"
                        value={form.content}
                        onChange={handleChange}
                        placeholder="Redigez le contenu de l'annonce..."
                        error={errors.content?.[0]}
                        required
                    />
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
                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" loading={loading}>
                            {isEdit ? 'Enregistrer' : "Publier l'annonce"}
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
