import { useState, useEffect } from 'react';
import api from '../../services/api';
import Button from '../ui/Button';
import Card from '../ui/Card';
import Alert from '../ui/Alert';
import Modal from '../ui/Modal';

export default function Subscription() {
    const [subscription, setSubscription] = useState(null);
    const [loading, setLoading] = useState(true);
    const [actionLoading, setActionLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    const [showCancelModal, setShowCancelModal] = useState(false);

    useEffect(() => {
        api.get('/subscription')
            .then(({ data }) => setSubscription(data.data || data))
            .catch(() => {})
            .finally(() => setLoading(false));
    }, []);

    const handleSubscribe = async () => {
        setError('');
        setActionLoading(true);
        try {
            const { data } = await api.post('/subscription');
            if (data.checkout_url) {
                window.location.href = data.checkout_url;
            } else {
                setSuccess('Abonnement active avec succes !');
                setSubscription(data.data || data);
            }
        } catch (err) {
            setError(err.response?.data?.message || "Erreur lors de l'abonnement.");
        } finally {
            setActionLoading(false);
        }
    };

    const handleCancel = async () => {
        setError('');
        setActionLoading(true);
        try {
            const { data } = await api.delete('/subscription');
            setSubscription(data.data || data);
            setSuccess('Abonnement resilie avec succes.');
            setShowCancelModal(false);
        } catch (err) {
            setError(err.response?.data?.message || "Erreur lors de la resiliation.");
        } finally {
            setActionLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center py-20">
                <div className="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin" />
            </div>
        );
    }

    const isActive = subscription?.status === 'active';

    return (
        <div className="max-w-2xl mx-auto space-y-8">
            <div>
                <h1 className="text-2xl font-bold text-gray-900">Abonnement</h1>
                <p className="text-gray-500 mt-1">Gerez votre abonnement CityPulse</p>
            </div>

            {error && <Alert type="error" onDismiss={() => setError('')}>{error}</Alert>}
            {success && <Alert type="success" onDismiss={() => setSuccess('')}>{success}</Alert>}

            {/* Plan card */}
            <div className="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-700 rounded-3xl p-8 sm:p-10 text-white">
                <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2" />
                <div className="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2" />
                <div className="relative">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-sm font-medium mb-6">
                        {isActive ? (
                            <>
                                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                                Abonnement actif
                            </>
                        ) : (
                            <>
                                <span className="w-2 h-2 rounded-full bg-gray-300" />
                                Aucun abonnement
                            </>
                        )}
                    </div>
                    <h2 className="text-3xl font-bold">Plan CityPulse</h2>
                    <div className="mt-4 flex items-baseline gap-1">
                        <span className="text-5xl font-extrabold">80</span>
                        <span className="text-xl font-semibold text-white/70">EUR/mois</span>
                    </div>
                    <p className="mt-3 text-white/70">Acces complet a toutes les fonctionnalites pour votre commune.</p>
                </div>
            </div>

            {/* Features included */}
            <Card title="Inclus dans votre plan">
                <ul className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {[
                        'Doleances illimitees',
                        'Gestion des evenements',
                        'Alertes en temps reel',
                        'Annonces officielles',
                        'Gestion des interventions',
                        'Flotte de vehicules',
                        'Tableau de bord analytique',
                        'Support prioritaire',
                    ].map((feature) => (
                        <li key={feature} className="flex items-center gap-2 text-sm text-gray-700">
                            <svg className="w-5 h-5 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            {feature}
                        </li>
                    ))}
                </ul>
            </Card>

            {/* Subscription details */}
            {isActive && subscription && (
                <Card title="Details de l'abonnement">
                    <div className="space-y-3">
                        <div className="flex items-center justify-between py-2">
                            <span className="text-sm text-gray-500">Statut</span>
                            <span className="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700">
                                <span className="w-2 h-2 rounded-full bg-emerald-500" />
                                Actif
                            </span>
                        </div>
                        {subscription.current_period_end && (
                            <div className="flex items-center justify-between py-2 border-t border-gray-50">
                                <span className="text-sm text-gray-500">Prochain renouvellement</span>
                                <span className="text-sm font-medium text-gray-900">
                                    {new Date(subscription.current_period_end).toLocaleDateString('fr-FR', {
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric',
                                    })}
                                </span>
                            </div>
                        )}
                    </div>
                </Card>
            )}

            {/* Actions */}
            <div className="flex items-center gap-4">
                {!isActive ? (
                    <Button loading={actionLoading} onClick={handleSubscribe} size="lg">
                        S'abonner - 80 EUR/mois
                    </Button>
                ) : (
                    <Button variant="danger" onClick={() => setShowCancelModal(true)}>
                        Resilier l'abonnement
                    </Button>
                )}
            </div>

            {/* Cancel modal */}
            {showCancelModal && (
                <Modal title="Resilier l'abonnement" onClose={() => setShowCancelModal(false)} size="sm">
                    <p className="text-gray-600 mb-6">
                        Etes-vous sur de vouloir resilier votre abonnement ? Vous perdrez l'acces a toutes les
                        fonctionnalites a la fin de votre periode de facturation actuelle.
                    </p>
                    <div className="flex items-center gap-3 justify-end">
                        <Button variant="secondary" onClick={() => setShowCancelModal(false)}>
                            Garder mon abonnement
                        </Button>
                        <Button variant="danger" loading={actionLoading} onClick={handleCancel}>
                            Confirmer la resiliation
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}
