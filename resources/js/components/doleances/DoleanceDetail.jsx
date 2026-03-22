import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import api from '../../services/api';
import { useAuth } from '../../contexts/AuthContext';
import Button from '../ui/Button';
import Badge from '../ui/Badge';
import Card from '../ui/Card';
import Alert from '../ui/Alert';
import Input from '../ui/Input';
import Modal from '../ui/Modal';

const statusSteps = [
    { key: 'nouvelle', label: 'Nouvelle', color: 'bg-blue-500' },
    { key: 'en_cours', label: 'En cours', color: 'bg-amber-500' },
    { key: 'resolue', label: 'Resolue', color: 'bg-emerald-500' },
];

export default function DoleanceDetail() {
    const { uuid } = useParams();
    const navigate = useNavigate();
    const { user } = useAuth();

    const [doleance, setDoleance] = useState(null);
    const [loading, setLoading] = useState(true);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [deleting, setDeleting] = useState(false);

    // Admin response
    const [response, setResponse] = useState('');
    const [newStatus, setNewStatus] = useState('');
    const [responding, setResponding] = useState(false);
    const [responseError, setResponseError] = useState('');
    const [responseSuccess, setResponseSuccess] = useState('');

    const isStaff = ['maire', 'secretaire', 'agent'].includes(user?.role);
    const isOwner = doleance?.user_uuid === user?.uuid || doleance?.user?.uuid === user?.uuid;
    const canEdit = isOwner && doleance?.status === 'nouvelle';

    useEffect(() => {
        api.get(`/doleances/${uuid}`)
            .then(({ data }) => setDoleance(data.data || data))
            .catch(() => navigate('/doleances'))
            .finally(() => setLoading(false));
    }, [uuid, navigate]);

    const handleDelete = async () => {
        setDeleting(true);
        try {
            await api.delete(`/doleances/${uuid}`);
            navigate('/doleances');
        } catch {
            setDeleting(false);
            setShowDeleteModal(false);
        }
    };

    const handleResponse = async (e) => {
        e.preventDefault();
        setResponseError('');
        setResponseSuccess('');
        setResponding(true);
        try {
            const payload = {};
            if (response) payload.response = response;
            if (newStatus) payload.status = newStatus;
            const { data } = await api.put(`/doleances/${uuid}`, payload);
            setDoleance(data.data || data);
            setResponse('');
            setNewStatus('');
            setResponseSuccess('Reponse enregistree avec succes.');
        } catch (err) {
            setResponseError(err.response?.data?.message || 'Erreur lors de la reponse.');
        } finally {
            setResponding(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20">
                <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
            </div>
        );
    }

    if (!doleance) return null;

    const currentStepIndex = statusSteps.findIndex((s) => s.key === doleance.status);

    return (
        <div className="max-w-3xl mx-auto space-y-6">
            {/* Back */}
            <button
                onClick={() => navigate('/doleances')}
                className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition-colors"
            >
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Retour aux doleances
            </button>

            {/* Header */}
            <Card>
                <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div className="flex-1">
                        <div className="flex items-center gap-3 flex-wrap">
                            <Badge variant={doleance.status} />
                            {doleance.priority && <Badge variant={doleance.priority} />}
                            <span className="text-xs text-gray-400 capitalize">{doleance.category}</span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold text-gray-900 mt-3">{doleance.title}</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Soumise le {new Date(doleance.created_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })}
                            {doleance.user && ` par ${doleance.user.first_name} ${doleance.user.last_name}`}
                        </p>
                    </div>
                    {canEdit && (
                        <div className="flex items-center gap-2">
                            <Link to={`/doleances/${uuid}/edit`}>
                                <Button variant="outline" size="sm">Modifier</Button>
                            </Link>
                            <Button variant="danger" size="sm" onClick={() => setShowDeleteModal(true)}>
                                Supprimer
                            </Button>
                        </div>
                    )}
                </div>

                {/* Description */}
                {doleance.description && (
                    <div className="mt-6 pt-6 border-t border-gray-100">
                        <p className="text-gray-700 whitespace-pre-wrap leading-relaxed">{doleance.description}</p>
                    </div>
                )}
            </Card>

            {/* Status timeline */}
            <Card title="Suivi du statut">
                <div className="flex items-center gap-0">
                    {statusSteps.map((step, i) => {
                        const isActive = i <= currentStepIndex;
                        const isRejected = doleance.status === 'rejetee';

                        return (
                            <div key={step.key} className="flex-1 flex items-center">
                                <div className="flex flex-col items-center flex-1">
                                    <div
                                        className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all ${
                                            isRejected && i === 0
                                                ? 'bg-red-500 text-white'
                                                : isActive
                                                ? `${step.color} text-white shadow-lg`
                                                : 'bg-gray-100 text-gray-400'
                                        }`}
                                    >
                                        {isActive ? (
                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        ) : (
                                            i + 1
                                        )}
                                    </div>
                                    <span className={`mt-2 text-xs font-medium ${isActive ? 'text-gray-900' : 'text-gray-400'}`}>
                                        {step.label}
                                    </span>
                                </div>
                                {i < statusSteps.length - 1 && (
                                    <div className={`h-0.5 flex-1 -mt-6 ${i < currentStepIndex ? step.color : 'bg-gray-100'}`} />
                                )}
                            </div>
                        );
                    })}
                </div>
                {doleance.status === 'rejetee' && (
                    <div className="mt-4 p-3 rounded-xl bg-red-50 border border-red-100">
                        <p className="text-sm text-red-700 font-medium">Cette doleance a ete rejetee.</p>
                    </div>
                )}
            </Card>

            {/* Admin response */}
            {doleance.admin_response && (
                <Card title="Reponse de la mairie">
                    <p className="text-gray-700 whitespace-pre-wrap">{doleance.admin_response}</p>
                </Card>
            )}

            {/* Staff respond form */}
            {isStaff && (
                <Card title="Repondre / Mettre a jour">
                    {responseError && <div className="mb-4"><Alert type="error">{responseError}</Alert></div>}
                    {responseSuccess && <div className="mb-4"><Alert type="success">{responseSuccess}</Alert></div>}
                    <form onSubmit={handleResponse} className="space-y-4">
                        <Input
                            label="Reponse"
                            type="textarea"
                            value={response}
                            onChange={(e) => setResponse(e.target.value)}
                            placeholder="Redigez votre reponse..."
                        />
                        <Input
                            label="Changer le statut"
                            type="select"
                            value={newStatus}
                            onChange={(e) => setNewStatus(e.target.value)}
                        >
                            <option value="">Ne pas changer</option>
                            <option value="nouvelle">Nouvelle</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolue">Resolue</option>
                            <option value="rejetee">Rejetee</option>
                        </Input>
                        <Button type="submit" loading={responding}>
                            Envoyer la reponse
                        </Button>
                    </form>
                </Card>
            )}

            {/* Delete modal */}
            {showDeleteModal && (
                <Modal title="Supprimer la doleance" onClose={() => setShowDeleteModal(false)} size="sm">
                    <p className="text-gray-600 mb-6">
                        Etes-vous sur de vouloir supprimer cette doleance ? Cette action est irreversible.
                    </p>
                    <div className="flex items-center gap-3 justify-end">
                        <Button variant="secondary" onClick={() => setShowDeleteModal(false)}>
                            Annuler
                        </Button>
                        <Button variant="danger" loading={deleting} onClick={handleDelete}>
                            Supprimer
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}
