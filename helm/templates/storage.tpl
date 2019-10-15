{{- define "storage.template" -}}
apiVersion: v1
kind: PersistentVolume
metadata:
  name: {{ .service.name | quote }}
spec:
  storageClassName: {{ .service.volume.class | default manual | quote }}
  capacity:
    storage: {{ .service.volume.storage | default 15Gi | quote }}
  accessModes:
    - ReadWriteMany
  hostPath:
    path: {{ .service.volume.path | default "/data" | quote }}
---
kind: PersistentVolumeClaim
apiVersion: v1
metadata:
  name: {{ .service.name | quote }}
spec:
  storageClassName: {{ .service.volume.class | default manual | quote }}
  accessModes:
    - ReadWriteMany
  resources:
    requests:
      storage: {{ .service.volume.storage | default 15Gi | quote }}
{{- end -}}
