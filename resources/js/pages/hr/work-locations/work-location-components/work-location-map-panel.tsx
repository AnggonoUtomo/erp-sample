import { FieldInfoLabel } from '@/components/field-info-label';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Loader2, LocateFixed, MapPinned } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { WorkLocationForm as WorkLocationFormData, WorkLocationPageProps } from '../types';

type GoogleLatLng = {
    lat: () => number;
    lng: () => number;
};

type GoogleMap = {
    setCenter: (position: { lat: number; lng: number }) => void;
    addListener: (eventName: string, callback: (event: { latLng?: GoogleLatLng }) => void) => void;
};

type GoogleMarker = {
    setPosition: (position: { lat: number; lng: number }) => void;
    addListener: (eventName: string, callback: (event: { latLng?: GoogleLatLng }) => void) => void;
};

type GoogleMapsNamespace = {
    maps: {
        Map: new (element: HTMLElement, options: Record<string, unknown>) => GoogleMap;
        Marker: new (options: Record<string, unknown>) => GoogleMarker;
    };
};

type WindowWithGoogleMaps = Window &
    typeof globalThis & {
        google?: GoogleMapsNamespace;
        __workLocationGoogleMapsPromise?: Promise<void>;
    };

type Props = {
    form: InertiaFormProps<WorkLocationFormData>;
    mapSettings: WorkLocationPageProps['mapSettings'];
};

const defaultPosition = {
    lat: -6.2,
    lng: 106.816666,
};

function parseCoordinate(value: string, fallback: number) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : fallback;
}

function loadGoogleMaps(apiKey: string) {
    const win = window as WindowWithGoogleMaps;

    if (win.google?.maps) {
        return Promise.resolve();
    }

    if (win.__workLocationGoogleMapsPromise) {
        return win.__workLocationGoogleMapsPromise;
    }

    win.__workLocationGoogleMapsPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');

        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&v=weekly`;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Google Maps gagal dimuat.'));

        document.head.appendChild(script);
    });

    return win.__workLocationGoogleMapsPromise;
}

export function WorkLocationMapPanel({ form, mapSettings }: Props) {
    const mapRef = useRef<HTMLDivElement | null>(null);
    const mapInstance = useRef<GoogleMap | null>(null);
    const markerInstance = useRef<GoogleMarker | null>(null);
    const [mapState, setMapState] = useState<'idle' | 'loading' | 'ready' | 'error'>('idle');

    const position = useMemo(
        () => ({
            lat: parseCoordinate(form.data.latitude, defaultPosition.lat),
            lng: parseCoordinate(form.data.longitude, defaultPosition.lng),
        }),
        [form.data.latitude, form.data.longitude],
    );

    const setCoordinates = useCallback(
        (lat: number, lng: number) => {
            form.setData('latitude', lat.toFixed(7));
            form.setData('longitude', lng.toFixed(7));
        },
        [form],
    );

    useEffect(() => {
        if (!mapSettings.enabled || !mapSettings.google_maps_api_key || !mapRef.current) {
            return;
        }

        if (mapInstance.current && markerInstance.current) {
            return;
        }

        let mounted = true;
        setMapState('loading');
        const initialPosition = position;

        loadGoogleMaps(mapSettings.google_maps_api_key)
            .then(() => {
                if (!mounted || !mapRef.current) {
                    return;
                }

                const google = (window as WindowWithGoogleMaps).google;

                if (!google?.maps) {
                    throw new Error('Google Maps tidak tersedia.');
                }

                const map = new google.maps.Map(mapRef.current, {
                    center: initialPosition,
                    zoom: form.data.latitude && form.data.longitude ? 16 : 11,
                    mapId: mapSettings.google_maps_map_id || undefined,
                    streetViewControl: false,
                    fullscreenControl: false,
                    mapTypeControl: false,
                });
                const marker = new google.maps.Marker({
                    map,
                    position: initialPosition,
                    draggable: true,
                });

                map.addListener('click', (event) => {
                    if (!event.latLng) {
                        return;
                    }

                    const nextPosition = {
                        lat: event.latLng.lat(),
                        lng: event.latLng.lng(),
                    };

                    marker.setPosition(nextPosition);
                    setCoordinates(nextPosition.lat, nextPosition.lng);
                });

                marker.addListener('dragend', (event) => {
                    if (!event.latLng) {
                        return;
                    }

                    setCoordinates(event.latLng.lat(), event.latLng.lng());
                });

                mapInstance.current = map;
                markerInstance.current = marker;
                setMapState('ready');
            })
            .catch(() => {
                if (mounted) {
                    setMapState('error');
                }
            });

        return () => {
            mounted = false;
        };
    }, [
        form.data.latitude,
        form.data.longitude,
        mapSettings.enabled,
        mapSettings.google_maps_api_key,
        mapSettings.google_maps_map_id,
        position,
        setCoordinates,
    ]);

    useEffect(() => {
        mapInstance.current?.setCenter(position);
        markerInstance.current?.setPosition(position);
    }, [position]);

    const showMap = mapSettings.enabled && mapSettings.google_maps_api_key;

    return (
        <div className="bg-background/60 space-y-3 rounded-lg border p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <FieldInfoLabel description="Klik area map atau drag marker untuk mengisi latitude dan longitude lokasi kerja.">
                        Titik Lokasi Map
                    </FieldInfoLabel>
                    <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                        Koordinat dan radius ini menjadi dasar lokasi kantor, attendance area, dan validasi geofence.
                    </p>
                </div>
                <Badge variant={showMap ? 'default' : 'secondary'}>{showMap ? 'Map aktif' : 'Perlu konfigurasi'}</Badge>
            </div>

            <div className="bg-muted/40 relative h-72 overflow-hidden rounded-lg border">
                {showMap ? (
                    <>
                        <div ref={mapRef} className="h-full w-full" />
                        {mapState === 'loading' && (
                            <div className="bg-background/70 text-muted-foreground absolute inset-0 flex items-center justify-center text-sm">
                                <Loader2 className="mr-2 size-4 animate-spin" />
                                Memuat Google Maps...
                            </div>
                        )}
                        {mapState === 'error' && (
                            <div className="bg-background/90 text-destructive absolute inset-0 flex items-center justify-center p-5 text-center text-sm">
                                Google Maps gagal dimuat. Periksa API Key, domain restriction, atau koneksi.
                            </div>
                        )}
                    </>
                ) : (
                    <div className="flex h-full flex-col items-center justify-center p-6 text-center">
                        <span className="dashboard-icon icon-tone-sky flex size-12 items-center justify-center rounded-lg">
                            <MapPinned className="size-5" />
                        </span>
                        <p className="mt-3 text-sm font-medium">Google Maps belum aktif</p>
                        <p className="text-muted-foreground mt-1 max-w-md text-xs leading-relaxed">
                            Isi API Key dan Map ID dari menu Console System Settings agar panel ini berubah menjadi map interaktif.
                        </p>
                    </div>
                )}
            </div>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="latitude" description="Nilai latitude hasil klik map. Bisa diedit manual jika diperlukan.">
                        Latitude
                    </FieldInfoLabel>
                    <Input
                        id="latitude"
                        value={form.data.latitude}
                        placeholder="-6.2000000"
                        onChange={(event) => form.setData('latitude', event.target.value)}
                    />
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="longitude" description="Nilai longitude hasil klik map. Bisa diedit manual jika diperlukan.">
                        Longitude
                    </FieldInfoLabel>
                    <Input
                        id="longitude"
                        value={form.data.longitude}
                        placeholder="106.8166660"
                        onChange={(event) => form.setData('longitude', event.target.value)}
                    />
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="geofence_radius_meters"
                        description="Radius toleransi dalam meter untuk validasi area check-in/check-out. Kosongkan jika lokasi tidak memakai geofence."
                    >
                        Radius Geofence
                    </FieldInfoLabel>
                    <Input
                        id="geofence_radius_meters"
                        type="number"
                        min="0"
                        max="100000"
                        value={form.data.geofence_radius_meters}
                        placeholder="100"
                        onChange={(event) => form.setData('geofence_radius_meters', event.target.value)}
                    />
                </div>
                <Button
                    type="button"
                    variant="outline"
                    className="lg:self-end"
                    onClick={() => setCoordinates(defaultPosition.lat, defaultPosition.lng)}
                >
                    <LocateFixed className="size-4" />
                    Jakarta
                </Button>
            </div>
        </div>
    );
}
