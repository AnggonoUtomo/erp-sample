import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Crop, ImageIcon } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

const OUTPUT_SIZE = 512;
const MIN_CROP_SIZE = 72;

type Point = {
    x: number;
    y: number;
};

type Size = {
    width: number;
    height: number;
};

type CropArea = Point & {
    size: number;
};

type ResizeCorner = 'nw' | 'ne' | 'sw' | 'se';

type DragState =
    | {
          type: 'move';
          pointerId: number;
          start: Point;
          origin: CropArea;
      }
    | {
          type: 'resize';
          pointerId: number;
          corner: ResizeCorner;
          start: Point;
          origin: CropArea;
      };

type Props = {
    file: File | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onApply: (file: File) => void;
};

export function ImageCropDialog({ file, open, onOpenChange, onApply }: Props) {
    const imageRef = useRef<HTMLImageElement>(null);
    const viewportRef = useRef<HTMLDivElement>(null);
    const dragRef = useRef<DragState | null>(null);
    const [imageSize, setImageSize] = useState<Size | null>(null);
    const [viewportSize, setViewportSize] = useState(320);
    const [cropArea, setCropArea] = useState<CropArea | null>(null);
    const [processing, setProcessing] = useState(false);

    const sourceUrl = useMemo(() => (file ? URL.createObjectURL(file) : null), [file]);

    useEffect(() => {
        return () => {
            if (sourceUrl) {
                URL.revokeObjectURL(sourceUrl);
            }
        };
    }, [sourceUrl]);

    useEffect(() => {
        if (open) {
            setImageSize(null);
            setCropArea(null);
        }
    }, [open, file]);

    useEffect(() => {
        const viewport = viewportRef.current;

        if (!open || !viewport) {
            return;
        }

        const observer = new ResizeObserver(([entry]) => setViewportSize(entry.contentRect.width));
        observer.observe(viewport);
        setViewportSize(viewport.getBoundingClientRect().width);

        return () => observer.disconnect();
    }, [open]);

    const displayedImage = useMemo(() => {
        if (!imageSize) {
            return null;
        }

        const scale = Math.min(viewportSize / imageSize.width, viewportSize / imageSize.height);
        const width = imageSize.width * scale;
        const height = imageSize.height * scale;

        return {
            x: (viewportSize - width) / 2,
            y: (viewportSize - height) / 2,
            width,
            height,
            scale,
        };
    }, [imageSize, viewportSize]);

    useEffect(() => {
        if (!displayedImage) {
            return;
        }

        const size = Math.min(displayedImage.width, displayedImage.height) * 0.72;
        setCropArea({
            x: displayedImage.x + (displayedImage.width - size) / 2,
            y: displayedImage.y + (displayedImage.height - size) / 2,
            size,
        });
    }, [displayedImage]);

    const clampCrop = (area: CropArea): CropArea => {
        if (!displayedImage) {
            return area;
        }

        const maxSize = Math.min(displayedImage.width, displayedImage.height);
        const size = Math.min(maxSize, Math.max(MIN_CROP_SIZE, area.size));

        return {
            size,
            x: Math.min(displayedImage.x + displayedImage.width - size, Math.max(displayedImage.x, area.x)),
            y: Math.min(displayedImage.y + displayedImage.height - size, Math.max(displayedImage.y, area.y)),
        };
    };

    const startMove = (event: React.PointerEvent<HTMLDivElement>) => {
        if (!cropArea) {
            return;
        }

        event.currentTarget.setPointerCapture(event.pointerId);
        dragRef.current = {
            type: 'move',
            pointerId: event.pointerId,
            start: { x: event.clientX, y: event.clientY },
            origin: cropArea,
        };
    };

    const startResize = (event: React.PointerEvent<HTMLButtonElement>, corner: ResizeCorner) => {
        if (!cropArea) {
            return;
        }

        event.stopPropagation();
        event.currentTarget.setPointerCapture(event.pointerId);
        dragRef.current = {
            type: 'resize',
            pointerId: event.pointerId,
            corner,
            start: { x: event.clientX, y: event.clientY },
            origin: cropArea,
        };
    };

    const handlePointerMove = (event: React.PointerEvent<HTMLElement>) => {
        const drag = dragRef.current;

        if (!drag || drag.pointerId !== event.pointerId) {
            return;
        }

        const deltaX = event.clientX - drag.start.x;
        const deltaY = event.clientY - drag.start.y;

        if (drag.type === 'move') {
            setCropArea(
                clampCrop({
                    ...drag.origin,
                    x: drag.origin.x + deltaX,
                    y: drag.origin.y + deltaY,
                }),
            );
            return;
        }

        const growsLeft = drag.corner === 'nw' || drag.corner === 'sw';
        const growsUp = drag.corner === 'nw' || drag.corner === 'ne';
        const sizeDelta = growsLeft ? -deltaX : deltaX;
        const verticalDelta = growsUp ? -deltaY : deltaY;
        const nextSize = drag.origin.size + (Math.abs(sizeDelta) > Math.abs(verticalDelta) ? sizeDelta : verticalDelta);
        const size = Math.max(MIN_CROP_SIZE, nextSize);

        setCropArea(
            clampCrop({
                size,
                x: growsLeft ? drag.origin.x + drag.origin.size - size : drag.origin.x,
                y: growsUp ? drag.origin.y + drag.origin.size - size : drag.origin.y,
            }),
        );
    };

    const stopDragging = (event: React.PointerEvent<HTMLElement>) => {
        if (dragRef.current?.pointerId === event.pointerId) {
            dragRef.current = null;
        }
    };

    const applyCrop = async () => {
        const image = imageRef.current;

        if (!file || !image || !displayedImage || !cropArea) {
            return;
        }

        setProcessing(true);

        try {
            const sourceX = (cropArea.x - displayedImage.x) / displayedImage.scale;
            const sourceY = (cropArea.y - displayedImage.y) / displayedImage.scale;
            const sourceSize = cropArea.size / displayedImage.scale;
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            if (!context) {
                throw new Error('Canvas is not supported.');
            }

            canvas.width = OUTPUT_SIZE;
            canvas.height = OUTPUT_SIZE;
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, OUTPUT_SIZE, OUTPUT_SIZE);
            context.drawImage(image, sourceX, sourceY, sourceSize, sourceSize, 0, 0, OUTPUT_SIZE, OUTPUT_SIZE);

            const blob = await new Promise<Blob>((resolve, reject) => {
                canvas.toBlob((result) => (result ? resolve(result) : reject(new Error('Failed to crop image.'))), 'image/jpeg', 0.9);
            });
            const baseName = file.name.replace(/\.[^/.]+$/, '') || 'avatar';

            onApply(new File([blob], `${baseName}-cropped.jpg`, { type: 'image/jpeg' }));
            onOpenChange(false);
        } finally {
            setProcessing(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={(nextOpen) => !processing && onOpenChange(nextOpen)}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="bg-primary/10 text-primary flex size-10 items-center justify-center rounded-lg">
                            <Crop className="size-5" />
                        </div>
                        <div>
                            <DialogTitle>Crop avatar</DialogTitle>
                            <DialogDescription className="mt-1">Geser kotak crop dan tarik sudutnya menggunakan cursor.</DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div
                    ref={viewportRef}
                    className="bg-muted relative mx-auto aspect-square w-full max-w-80 touch-none overflow-hidden rounded-lg"
                    onPointerMove={handlePointerMove}
                    onPointerUp={stopDragging}
                    onPointerCancel={stopDragging}
                >
                    {sourceUrl ? (
                        <img
                            ref={imageRef}
                            src={sourceUrl}
                            alt="Avatar crop preview"
                            draggable={false}
                            onLoad={(event) =>
                                setImageSize({
                                    width: event.currentTarget.naturalWidth,
                                    height: event.currentTarget.naturalHeight,
                                })
                            }
                            className="pointer-events-none size-full object-contain select-none"
                        />
                    ) : (
                        <div className="text-muted-foreground flex size-full items-center justify-center">
                            <ImageIcon className="size-8" />
                        </div>
                    )}

                    {cropArea && (
                        <div
                            role="presentation"
                            className="absolute cursor-move border-2 border-white shadow-[0_0_0_999px_rgba(0,0,0,0.48)]"
                            style={{
                                left: cropArea.x,
                                top: cropArea.y,
                                width: cropArea.size,
                                height: cropArea.size,
                            }}
                            onPointerDown={startMove}
                        >
                            <div className="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3">
                                {Array.from({ length: 9 }).map((_, index) => (
                                    <span key={index} className="border border-white/25" />
                                ))}
                            </div>
                            {(['nw', 'ne', 'sw', 'se'] as ResizeCorner[]).map((corner) => (
                                <button
                                    key={corner}
                                    type="button"
                                    aria-label={`Resize crop ${corner}`}
                                    className={`border-primary bg-background absolute size-4 border-2 ${
                                        corner === 'nw'
                                            ? '-top-2 -left-2 cursor-nwse-resize'
                                            : corner === 'ne'
                                              ? '-top-2 -right-2 cursor-nesw-resize'
                                              : corner === 'sw'
                                                ? '-bottom-2 -left-2 cursor-nesw-resize'
                                                : '-right-2 -bottom-2 cursor-nwse-resize'
                                    }`}
                                    onPointerDown={(event) => startResize(event, corner)}
                                    onPointerMove={handlePointerMove}
                                    onPointerUp={stopDragging}
                                    onPointerCancel={stopDragging}
                                />
                            ))}
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
                        Batal
                    </Button>
                    <Button type="button" onClick={applyCrop} disabled={processing || !cropArea}>
                        <Crop className="size-4" />
                        {processing ? 'Memproses...' : 'Gunakan Gambar'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
